<form action="{{ $route }}" method="POST" style="display:inline;">
    @csrf
    @method('PATCH')
    <button type="submit">{{ $label }}</button>
</form>
