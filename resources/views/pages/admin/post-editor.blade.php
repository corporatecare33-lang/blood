{{-- /admin/blog/new and /admin/blog/{post}/edit (Route::view passes {post} as $post). --}}
<x-layouts.admin :title="isset($post) ? 'লেখা সম্পাদনা' : 'নতুন লেখা'">
    <livewire:admin.post-editor :post-id="isset($post) ? (int) $post : null" />
</x-layouts.admin>
