@if(count($crumbs))
  <div id="breadcrumb"{{ $markup ? ' itemscope itemtype=http://schema.org/BreadcrumbList' : '' }}>
    <div class="breadcrumbs">
      @foreach($crumbs as $crumb)
        @if(!$loop->first)
          <span class="delimiter{{ $bootstrap ? ' glyphicon' : '' }}">{!! $separator !!}</span>
        @endif
        @if(!$loop->last)
          <span{{ $markup ? ' itemprop=itemListElement itemscope itemtype=http://schema.org/ListItem' : '' }}>
            <a href="{{ $crumb['url'] }}" title="{{ $crumb['name'] }}"{{ $markup ? ' itemprop=item' : '' }}>
              <span{{ $markup ? ' itemprop=name' : '' }}>{{ $crumb['name'] }}</span>
            </a>
            @if($markup)
              <meta itemprop="position" content="{{ $loop->iteration }}">
            @endif
          </span>
        @elseif($loop->last)
          <span{{ $markup ? ' itemprop=itemListElement itemscope itemtype=http://schema.org/ListItem' : ''}}>
            <span{{ $markup ? ' itemprop=name' : '' }}>
              {{ $crumb['name'] }}
            </span>
            @if($markup)
              <meta itemprop="position" content="{{ $loop->iteration }}">
            @endif
          </span>
        @endif
      @endforeach
    </div>
  </div>
@endif