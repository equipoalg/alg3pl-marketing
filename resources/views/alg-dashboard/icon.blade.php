@php
    /** @var string $name */
    /** @var int $size */
    /** @var string $stroke */
    $size = $size ?? 16;
    $stroke = $stroke ?? 'currentColor';
    $sw = $sw ?? 1.5;
    $svgAttrs = "width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 20 20\" fill=\"none\" stroke=\"{$stroke}\" stroke-width=\"{$sw}\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"flex-shrink:0;display:inline-block;vertical-align:middle;\"";
@endphp
@switch($name)
    @case('grid')          <svg {!! $svgAttrs !!}><rect x="3" y="3" width="6" height="6" rx="1"/><rect x="11" y="3" width="6" height="6" rx="1"/><rect x="3" y="11" width="6" height="6" rx="1"/><rect x="11" y="11" width="6" height="6" rx="1"/></svg> @break
    @case('columns')       <svg {!! $svgAttrs !!}><rect x="3" y="3" width="4" height="14" rx="1"/><rect x="9" y="3" width="4" height="14" rx="1"/><rect x="15" y="3" width="2" height="14" rx="1"/></svg> @break
    @case('table')         <svg {!! $svgAttrs !!}><rect x="3" y="4" width="14" height="12" rx="1"/><path d="M3 8h14M3 12h14M8 4v12"/></svg> @break
    @case('building')      <svg {!! $svgAttrs !!}><rect x="4" y="3" width="12" height="14" rx="1"/><path d="M7 7h2M11 7h2M7 10h2M11 10h2M7 13h2M11 13h2"/></svg> @break
    @case('user-plus')     <svg {!! $svgAttrs !!}><circle cx="8" cy="7" r="3"/><path d="M3 17c0-2.8 2.2-5 5-5s5 2.2 5 5M15 8v4M13 10h4"/></svg> @break
    @case('tag')           <svg {!! $svgAttrs !!}><path d="M9 3H4v5l8 8 5-5-8-8z"/><circle cx="6.5" cy="6.5" r="0.6" fill="currentColor"/></svg> @break
    @case('activity')      <svg {!! $svgAttrs !!}><path d="M3 10h3l2-5 4 10 2-5h3"/></svg> @break
    @case('megaphone')     <svg {!! $svgAttrs !!}><path d="M3 8v4l9 4V4l-9 4zM12 6v8M3 12h2v3"/></svg> @break
    @case('filter')        <svg {!! $svgAttrs !!}><path d="M3 4h14l-5 7v5l-4-2v-3L3 4z"/></svg> @break
    @case('mail')          <svg {!! $svgAttrs !!}><rect x="3" y="5" width="14" height="10" rx="1"/><path d="M3 6l7 5 7-5"/></svg> @break
    @case('send')          <svg {!! $svgAttrs !!}><path d="M17 3l-7 14-2-6-6-2 15-6z"/></svg> @break
    @case('trending-up')   <svg {!! $svgAttrs !!}><path d="M3 14l5-5 3 3 6-6M13 6h4v4"/></svg> @break
    @case('search')        <svg {!! $svgAttrs !!}><circle cx="9" cy="9" r="5"/><path d="M13 13l4 4"/></svg> @break
    @case('file-text')     <svg {!! $svgAttrs !!}><path d="M5 3h7l4 4v10H5z"/><path d="M12 3v4h4M7 11h6M7 14h6"/></svg> @break
    @case('chevron-down')  <svg {!! $svgAttrs !!}><path d="M5 8l5 5 5-5"/></svg> @break
    @case('chevron-right') <svg {!! $svgAttrs !!}><path d="M8 5l5 5-5 5"/></svg> @break
    @case('chevron-left')  <svg {!! $svgAttrs !!}><path d="M12 5l-5 5 5 5"/></svg> @break
    @case('plus')          <svg {!! $svgAttrs !!}><path d="M10 4v12M4 10h12"/></svg> @break
    @case('arrow-up')      <svg {!! $svgAttrs !!}><path d="M10 16V4M5 9l5-5 5 5"/></svg> @break
    @case('arrow-down')    <svg {!! $svgAttrs !!}><path d="M10 4v12M5 11l5 5 5-5"/></svg> @break
    @case('arrow-up-right')<svg {!! $svgAttrs !!}><path d="M6 14L14 6M7 6h7v7"/></svg> @break
    @case('calendar')      <svg {!! $svgAttrs !!}><rect x="3" y="4" width="14" height="13" rx="1"/><path d="M3 8h14M7 3v3M13 3v3"/></svg> @break
    @case('bell')          <svg {!! $svgAttrs !!}><path d="M5 13V9a5 5 0 0110 0v4l1 2H4l1-2zM8 17a2 2 0 004 0"/></svg> @break
    @case('settings')      <svg {!! $svgAttrs !!}><circle cx="10" cy="10" r="2.5"/><path d="M10 2v2M10 16v2M2 10h2M16 10h2M4.2 4.2l1.5 1.5M14.3 14.3l1.5 1.5M4.2 15.8l1.5-1.5M14.3 5.7l1.5-1.5"/></svg> @break
    @case('globe')         <svg {!! $svgAttrs !!}><circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3a10 10 0 010 14M10 3a10 10 0 000 14"/></svg> @break
    @case('info')          <svg {!! $svgAttrs !!}><circle cx="10" cy="10" r="7"/><path d="M10 9v5M10 6.5v0.5"/></svg> @break
    @case('filter-h')      <svg {!! $svgAttrs !!}><path d="M3 6h14M5 10h10M7 14h6"/></svg> @break
    @case('download')      <svg {!! $svgAttrs !!}><path d="M10 3v10M5 9l5 4 5-4M3 16h14"/></svg> @break
    @case('check')         <svg {!! $svgAttrs !!}><path d="M4 10l4 4 8-8"/></svg> @break
    @case('x')             <svg {!! $svgAttrs !!}><path d="M5 5l10 10M15 5l-10 10"/></svg> @break
@endswitch
