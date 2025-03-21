@extends('layouts.app')

@section('content')
    <h1>مشاهده اطلاعات کاربر</h1>
    <div>
        <strong>نام:</strong> {{ $user->name }}
    </div>
    <div>
        <strong>ایمیل:</strong> {{ $user->email }}
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">بازگشت به لیست کاربران</a>
@endsection