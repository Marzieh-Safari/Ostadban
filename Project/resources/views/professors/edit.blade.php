@extends('layouts.app')

@section('content')
    <h1>ویرایش استاد</h1>
    <form action="{{ route('professors.update', $professor->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">نام:</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $professor->name }}" required>
        </div>
        <div class="form-group">
            <label for="email">ایمیل:</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $professor->email }}" required>
        </div>
        <div class="form-group">
            <label for="department">دپارتمان:</label>
            <input type="text" name="department" id="department" class="form-control" value="{{ $professor->department }}" required>
        </div>
        <button type="submit" class="btn btn-success">ذخیره تغییرات</button>
    </form>
@endsection