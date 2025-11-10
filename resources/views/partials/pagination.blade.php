@php
  /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
  $infoLabel = $infoLabel ?? 'items';
  $last = max(1, $paginator->lastPage()); // ít nhất 1
@endphp

<div class="row">
  <div class="col-sm-5 text-center">
    <small class="text-muted inline m-t-sm m-b-sm">
      showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}
      of {{ $paginator->total() }} {{ $infoLabel }}
    </small>
  </div>

  <div class="col-sm-7 text-right text-center-xs">
    <ul class="pagination pagination-sm m-t-none m-b-none">
      {{-- Previous --}}
      <li class="{{ $paginator->onFirstPage() ? 'disabled' : '' }}">
        <a href="{{ $paginator->previousPageUrl() ?: 'javascript:;' }}">
          <i class="fa fa-chevron-left"></i>
        </a>
      </li>

      {{-- Page numbers (sẽ là 1 khi chỉ có 1 trang) --}}
      @for ($page = 1; $page <= $last; $page++)
        @php $url = $paginator->url($page); @endphp
        <li class="{{ $page == $paginator->currentPage() ? 'active' : '' }}">
          <a href="{{ $url }}">{{ $page }}</a>
        </li>
      @endfor

      {{-- Next --}}
      <li class="{{ $paginator->hasMorePages() ? '' : 'disabled' }}">
        <a href="{{ $paginator->nextPageUrl() ?: 'javascript:;' }}">
          <i class="fa fa-chevron-right"></i>
        </a>
      </li>
    </ul>
  </div>
</div>
