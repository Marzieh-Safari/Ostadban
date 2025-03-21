@extends('layouts.app')

@section('content')
    <h1>مشاهده نظر</h1>
    <div>
        <strong>کاربر:</strong> {{ $feedback->user->name }}
    </div>
    <div>
        <strong>استاد:</strong> {{ $feedback->professor->name }}
    </div>
    <div>
        <strong>امتیاز:</strong> {{ $feedback->rating }}
    </div>
    <div>
        <strong>نظر:</strong> {{ $feedback->comment }}
    </div>
    <a href="{{ route('feedbacks.index') }}" class="btn btn-secondary">بازگشت به لیست نظرات</a>
@endsection