@extends('layouts.app')

@section('content')
    <h1>لیست نظرات</h1>
    <table class="table">
        <thead>
            <tr>
                <th>کاربر</th>
                <th>استاد</th>
                <th>امتیاز</th>
                <th>نظر</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($feedbacks as $feedback)
                <tr>
                    <td>{{ $feedback->user->name }}</td>
                    <td>{{ $feedback->professor->name }}</td>
                    <td>{{ $feedback->rating }}</td>
                    <td>{{ $feedback->comment }}</td>
                    <td>
                        <a href="{{ route('feedbacks.show', $feedback->id) }}" class="btn btn-info">مشاهده</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection