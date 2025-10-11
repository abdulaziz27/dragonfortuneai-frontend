@php
    $navItems = [
        [
            'label' => 'On-Chain Valuation',
            'route' => 'onchain-metrics.valuation',
        ],
        [
            'label' => 'Supply & HODL Behavior',
            'route' => 'onchain-metrics.supply',
        ],
        [
            'label' => 'Exchange & Liquidity Flows',
            'route' => 'onchain-metrics.flows',
        ],
        [
            'label' => 'Miners & Whales',
            'route' => 'onchain-metrics.miners-whales',
        ],
    ];
@endphp

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="df-panel p-2 shadow-sm rounded bg-white">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="btn btn-sm fw-semibold px-3 py-1 {{ request()->routeIs($item['route']) ? 'btn-primary text-white shadow-sm' : 'btn-outline-primary' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
