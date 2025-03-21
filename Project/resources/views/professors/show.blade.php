@extends('layouts.app')

@section('content')
    <h1>مشاهده اطلاعات استاد</h1>
    <div>
        <strong>نام:</strong> {{ $professor->name }}
    </div>
    <div>
        <strong>ایمیل:</strong> {{ $professor->email }}
    </div>
    <div>
        <strong>دپارتمان:</strong> {{ $professor->department }}
    </div>
    <a href="{{ route('professors.index') }}" class="btn btn-secondary">بازگشت به لیست اساتید</a>
@endsection