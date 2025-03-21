@extends('layouts.app')

@section('content')
    <h1>ایجاد استاد جدید</h1>
    <form action="{{ route('professors.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">نام:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">ایمیل:</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="department">دپارتمان:</label>
            <input type="text" name="department" id="department" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">ذخیره</button>
    </form>
@endsection