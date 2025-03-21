@extends('layouts.app')

@section('content')
    <h1>لیست کاربران</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">ایجاد کاربر جدید</a>
    <table class="table">
        <thead>
            <tr>
                <th>نام</th>
                <th>ایمیل</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-info">مشاهده</a>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">ویرایش</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
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