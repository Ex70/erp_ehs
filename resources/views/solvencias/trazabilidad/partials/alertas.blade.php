@foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $clave => $clase)
    @if (session($clave))
        <div class="alert alert-{{ $clase }} alert-dismissible fade show sol-noprint">
            {{ session($clave) }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show sol-noprint">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
