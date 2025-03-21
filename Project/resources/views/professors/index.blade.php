@extends('layouts.app')

@section('content')
    <h1>لیست اساتید</h1>
    <a href="{{ route('professors.create') }}" class="btn btn-primary">ایجاد استاد جدید</a>
    <table class="table">
        <thead>
            <tr>
                <th>نام</th>
                <th>ایمیل</th>
                <th>دپارتمان</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($professors as $professor)
                <tr>
                    <td>{{ $professor->name }}</td>
                    <td>{{ $professor->email }}</td>
                    <td>{{ $professor->department }}</td>
                    <td>
                        <a href="{{ route('professors.show', $professor->id) }}" class="btn btn-info">مشاهده</a>
                        <a href="{{ route('professors.edit', $professor->id) }}" class="btn btn-warning">ویرایش</a>
                        <form action="{{ route('professors.destroy', $professor->id) }}" method="POST" style="display:inline;">
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