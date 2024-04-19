<style>
    .responsive-iframe {
        width: 100%;
        max-width: 100%;

        height: 70vh;
        border: none;
        border-radius: 15px;
    }
</style>

{{-- <iframe class=" responsive-iframe" src="{{ Storage::url($document->path) }}#toolbar=0"></iframe> --}}
<embed src="{{ Storage::url($document->path) }}" type="{{ Storage::mimeType($document->path) }}"
    class=" responsive-iframe">