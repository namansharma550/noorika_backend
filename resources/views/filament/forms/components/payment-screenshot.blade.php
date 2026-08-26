<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :state-path="$getStatePath()"
>
    @php $url = $getRecord()?->screenshot_url; @endphp
    @if ($url)
        <a href="{{ $url }}" target="_blank" rel="noopener">
            <img src="{{ $url }}" alt="Payment screenshot" style="max-width: 320px; border-radius: 8px; border: 1px solid #e5e7eb;" />
        </a>
    @endif
</x-dynamic-component>
