@extends('layouts.app')

@section('content')
    <h1>لیست دوره‌ها</h1>
    <a href="{{ route('courses.create') }}" class="btn btn-primary">ایجاد دوره جدید</a>
    <table class="table">
        <thead>
            <tr>
                <th>عنوان</th>
                <th>توضیحات</th>
                <th>استاد</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($courses as $course)
                <tr>
                    <td>{{ $course->title }}</td>
                    <td>{{ $course->description }}</td>
                    <td>{{ $course->professor->name }}</td>
                    <td>
                        <a href="{{ route('courses.show', $course->id) }}" class="btn btn-info">مشاهده</a>
                        <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-warning">ویرایش</a>
                        <form action="{{ route('courses.destroy', $course->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection