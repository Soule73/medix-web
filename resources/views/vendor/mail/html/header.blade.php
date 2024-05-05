@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <img src="https://res.cloudinary.com/dexsezu0j/image/upload/v1714831695/medix/uiyyzknhncnsogj7exnp.png"
                width="100" height="100" class="logo" alt="Laravel Logo">
            {{-- @if (trim($slot) === 'Laravel')
            @else --}}
            {{-- {{ $slot }} --}}
            {{-- @endif --}}
        </a>
    </td>
</tr>