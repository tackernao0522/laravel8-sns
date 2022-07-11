@csrf
<div class="md-form">
    <label>タイトル</label>
    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
</div>
<div class="form-group">
    <label></label>
    <textarea name="body" class="form-control" rows="16" placeholder="本文" required>{{ old('body') }}</textarea>
</div>
