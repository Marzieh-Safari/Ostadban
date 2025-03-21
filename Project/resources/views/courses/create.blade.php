@extends('layouts.app')

@section('content')
    <h1>ایجاد دوره جدید</h1>
    <form action="{{ route('courses.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="title">عنوان دوره:</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">توضیحات:</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="professor_id">استاد:</label>
            <select name="professor_id" id="professor_id" class="form-control" required>
                @foreach ($professors as $professor)
                    <option value="{{ $professor->id }}">{{ $professor->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">ذخیره</button>
    </form>
@endsection