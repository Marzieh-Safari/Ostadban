<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Collection;

class CourseController extends Controller
{
    const CACHE_TTL = 3600; // 1 hour in seconds
    const CACHE_PREFIX = 'api.course.';
    const SEARCH_CACHE_PREFIX = 'api.search.course.';

    /**
     * نمایش لیست دوره‌ها
     */
    public function index(Request $request)
    {
        $cacheKey = $this->buildCacheKey('index', $request);
        
        $course = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request) {
            $query = Course::with('professor');
            
            if ($request->has('faculty_number')) {
                $query->where('faculty_number', $request->faculty_number);
            }
            
            if ($request->has('sort_by')) {
                $direction = $request->get('sort_dir', 'asc');
                $query->orderBy($request->sort_by, $direction);
            } else {
                $query->orderBy('title');
            }
            
            return $query->get();
        });

        return response()->json([
            'data' => $course,
            'meta' => [
                'cached' => true,
                'cache_key' => $cacheKey,
                'expires_in' => self::CACHE_TTL . ' seconds'
            ]
        ]);
    }

    /**
     * نمایش جزئیات دوره
     */
    public function show($id)
    {
        $cacheKey = self::CACHE_PREFIX . 'show.' . $id;
        
        $course = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Course::with(['professor', 'prerequisite'])
                ->withCount(['enrollment', 'review'])
                ->withAvg('review', 'rating')
                ->findOrFail($id);
        });

        return response()->json([
            'data' => $course,
            'meta' => [
                'cached' => true,
                'expires_at' => now()->addSeconds(self::CACHE_TTL)->toDateTimeString()
            ]
        ]);
    }

    /**
     * ایجاد دوره جدید
     */
    public function store(Request $request)
    {
        $validated = $this->validateCourseRequest($request);
        $course = Course::create($validated);
        $this->clearRelatedCaches($course);

        return response()->json([
            'data' => $course,
            'message' => 'Course created successfully'
        ], 201);
    }

    /**
     * به‌روزرسانی دوره
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $validated = $this->validateCourseRequest($request, $id);
        $course->update($validated);
        $this->clearRelatedCaches($course);

        return response()->json([
            'data' => $course,
            'message' => 'Course updated successfully'
        ]);
    }

    /**
     * حذف دوره
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        $this->clearRelatedCaches($course);

        return response()->json(null, 204);
    }

    /**
     * جستجوی دوره‌ها
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $cacheKey = self::SEARCH_CACHE_PREFIX . md5($query);
        
        $course = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query) {
            return Course::with('professor')
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%");
                })
                ->limit(15)
                ->get();
        });

        return response()->json([
            'data' => $course,
            'meta' => ['cached' => true]
        ]);
    }

    /**
     * نمایش دوره‌های محبوب
     */
    public function popular()
    {
        $cacheKey = self::CACHE_PREFIX . 'popular';
        
        $course = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return Course::with('professor')
                ->withCount('enrollment')
                ->orderBy('enrollment_count', 'desc')
                ->limit(5)
                ->get();
        });

        return response()->json([
            'data' => $course,
            'meta' => ['cached' => true]
        ]);
    }

    // ==================== روش‌های کمکی ====================

    protected function validateCourseRequest(Request $request, $id = null)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'faculty_number' => 'required|exists:professor,id',
            'code' => 'required|unique:course,code,' . $id,
            'credit' => 'required|integer|min:1|max:6',
            'is_public' => 'sometimes|boolean'
        ]);
    }

    protected function buildCacheKey($method, $request)
    {
        $key = self::CACHE_PREFIX . $method;
        
        if ($request->hasAny(['faculty_number', 'sort_by', 'sort_dir'])) {
            $key .= '.' . md5(serialize($request->all()));
        }

        return $key;
    }

    protected function clearRelatedCaches($course)
    {
        if ($course instanceof Collection) {
            foreach ($course as $c) {
                $this->clearSingleCourseCache($c);
            }
        } else {
            $this->clearSingleCourseCache($course);
        }
        
        $this->clearListCaches();
        $this->clearSearchCaches();
    }

    protected function clearSingleCourseCache(Course $course)
    {
        Cache::forget(self::CACHE_PREFIX . 'show.' . $course->id);
        Cache::forget('api.professor.' . $course->faculty_number);
    }

    protected function clearListCaches()
    {
        Cache::forget(self::CACHE_PREFIX . 'index');
        Cache::forget(self::CACHE_PREFIX . 'popular');
    }

    protected function clearSearchCaches()
    {
        if (config('cache.default') === 'redis') {
            $redis = Redis::connection(config('cache.stores.redis.connection'));
            $keys = $redis->command('keys', [self::SEARCH_CACHE_PREFIX . '*']);
            
            foreach ($keys as $key) {
                $cleanKey = str_replace(config('database.redis.options.prefix'), '', $key);
                Cache::forget($cleanKey);
            }
        }
    }
}