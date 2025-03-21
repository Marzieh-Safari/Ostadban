@extends('layouts.app')

@section('content')
    <h1>مشاهده دوره</h1>
    <div>
        <strong>عنوان:</strong> {{ $course->title }}
    </div>
    <div>
        <strong>توضیحات:</strong> {{ $course->description }}
    </div>
    <div>
        <strong>استاد:</strong> {{ $course->professor->name }}
    </div>
    <a href="{{ route('courses.index') }}" class="btn btn-secondary">بازگشت به لیست دوره‌ها</a>
@endsection