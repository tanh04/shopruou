<nav aria-label="breadcrumb">
  <ol class="breadcrumb bg-transparent p-0 mb-3">
    @foreach ($items as $item)
      @php
        $isActive = $item['active'] ?? false;
        $label    = $item['label'] ?? '';
        $url      = $item['url']   ?? null;
        $icon     = $item['icon']  ?? null;
      @endphp

      @if ($isActive)
        <li class="breadcrumb-item active" aria-current="page">
          @if($icon) <i class="{{ $icon }}"></i> @endif
          {{ $label }}
        </li>
      @else
        <li class="breadcrumb-item">
          <a href="{{ $url ?: 'javascript:;' }}">
            @if($icon) <i class="{{ $icon }}"></i> @endif
            {{ $label }}
          </a>
        </li>
      @endif
    @endforeach
  </ol>
</nav>
<style>
    .breadcrumb { margin-bottom: 15px; }
    .breadcrumb .fa { margin-right: 6px; }
</style>