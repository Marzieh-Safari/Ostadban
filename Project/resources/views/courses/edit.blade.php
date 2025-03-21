@extends('layouts.app')

@section('content')
    <h1>ثبت فیدبک جدید</h1>

    {{-- نمایش ارورها (اگر اعتبارسنجی شکست بخورد) --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- فرم ثبت فیدبک --}}
    <form action="{{ route('feedbacks.store') }}" method="POST">
        @csrf {{-- لاراول نیاز به این توکن برای امنیت دارد --}}
        
        <div class="form-group">
            <label for="title">عنوان فیدبک:</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="form-group">
            <label for="content">محتوای فیدبک:</label>
            <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content') }}</textarea>
        </div>

        <div class="form-group">
            <label for="user_id">کاربر:</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="">انتخاب کنید...</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">ثبت فیدبک</button>
        <a href="{{ route('feedbacks.index') }}" class="btn btn-secondary">لغو</a>
    </form>
@endsection