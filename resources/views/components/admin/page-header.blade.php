@props([
    'breadcrumb' => [],
    'title',
    'subtitle' => null,
    'icon' => null,
])

<div class="admin-page-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div class="admin-page-header-main">
        @if(!empty($breadcrumb))
            <nav class="admin-breadcrumb mb-1" aria-label="Breadcrumb">
                <ol class="list-inline mb-0 d-flex flex-wrap align-items-center gap-1">
                    @foreach($breadcrumb as $index => $crumb)
                        <li class="list-inline-item d-inline-flex align-items-center gap-1 {{ $loop->last ? 'active text-dark fw-semibold' : 'text-muted' }}">
                            @if(!empty($crumb['url']) && !$loop->last)
                                <a href="{{ $crumb['url'] }}" class="text-decoration-none text-muted">{{ $crumb['label'] }}</a>
                            @else
                                <span>{{ $crumb['label'] }}</span>
                            @endif
                            @if(!$loop->last)
                                <span class="crumb-separator text-muted opacity-50">/</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <h1 class="admin-page-title mb-1 d-flex align-items-center gap-2">
            @if($icon)
                <i class="bi {{ $icon }} admin-page-icon"></i>
            @endif
            <span>{{ $title }}</span>
        </h1>

        @if($subtitle)
            <p class="admin-page-subtitle mb-0">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions) && $actions->isNotEmpty())
        <div class="admin-page-actions d-flex flex-wrap align-items-center gap-2">
            {{ $actions }}
        </div>
    @elseif(!empty($slot) && trim($slot) !== '')
        <div class="admin-page-actions d-flex flex-wrap align-items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
