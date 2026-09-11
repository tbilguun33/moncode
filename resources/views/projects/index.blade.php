@extends('layouts.app')

@section('title', 'Төслүүд — ' . config('app.name'))

@php use App\Enums\ReactionType; @endphp

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Төслүүд</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Community-д өөрийн хийсэн ажлаа хуваалцаарай</p>
        </div>

        @auth
            <button type="button" data-modal-open="create-project"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-linear-to-r from-indigo-500 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.35)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_25px_rgba(99,102,241,0.55)]">
                + Шинэ төсөл
            </button>
        @else
            <a href="{{ route('login') }}" class="rounded-lg bg-linear-to-r from-indigo-500 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.35)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_0_25px_rgba(99,102,241,0.55)]">
                Нэвтэрч нийтлэх
            </a>
        @endauth
    </div>

    @if ($projects->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
            Одоогоор төсөл алга. Хамгийн эхний төслийг та нийтлээрэй!
        </div>
    @else
        <div class="columns-1 gap-6 lg:columns-2">
            @foreach ($projects as $project)
                @php
                    $likeCount = $project->likes->where('type', ReactionType::Like)->count();
                    $dislikeCount = $project->likes->where('type', ReactionType::Dislike)->count();
                    $userReaction = auth()->check() ? optional($project->likes->firstWhere('user_id', auth()->id()))->type : null;
                @endphp
                <article class="group mb-6 break-inside-avoid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-xl dark:border-slate-800/80 dark:bg-slate-900/60 dark:shadow-none dark:backdrop-blur-xl dark:hover:border-indigo-500/50 dark:hover:shadow-[0_0_30px_rgba(99,102,241,0.15)]">
                    @if ($project->images->isNotEmpty())
                        <div class="grid gap-0.5 overflow-hidden {{ $project->images->count() === 1 ? 'grid-cols-1' : ($project->images->count() === 2 ? 'grid-cols-2' : 'grid-cols-3') }}">
                            @foreach ($project->images->sortBy('order') as $image)
                                <div class="overflow-hidden">
                                    <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $project->title }}"
                                         class="h-56 w-full scale-100 object-cover transition-transform duration-500 group-hover:scale-110">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            @if ($project->user->avatar)
                                <img src="{{ asset('storage/'.$project->user->avatar) }}" alt="{{ $project->user->name }}" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-cyan-500 text-sm font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.3)]">
                                    {{ strtoupper(mb_substr($project->user->name, 0, 1)) }}
                                </span>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $project->user->name }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $project->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-slate-100">{{ $project->title }}</h2>
                        @if ($project->description)
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $project->description }}</p>
                        @endif

                        <div class="mt-5 flex items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                            @auth
                                <form method="POST" action="{{ route('projects.react', $project) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="like">
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-150 active:scale-90 {{ $userReaction === ReactionType::Like ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5' }}">
                                        👍 {{ $likeCount }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('projects.react', $project) }}">
                                    @csrf
                                    <input type="hidden" name="type" value="dislike">
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-150 active:scale-90 {{ $userReaction === ReactionType::Dislike ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5' }}">
                                        👎 {{ $dislikeCount }}
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-400 dark:text-slate-500">👍 {{ $likeCount }}</span>
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-400 dark:text-slate-500">👎 {{ $dislikeCount }}</span>
                            @endauth
                        </div>

                        <details class="mt-2">
                            <summary class="cursor-pointer list-none text-sm font-medium text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">
                                💬 {{ $project->comments->count() }} сэтгэгдэл харах
                            </summary>

                            <div class="mt-3 space-y-3">
                                @foreach ($project->comments->sortBy('created_at') as $comment)
                                    <div class="flex gap-2 text-sm">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ strtoupper(mb_substr($comment->user->name, 0, 1)) }}
                                        </span>
                                        <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $comment->user->name }}</p>
                                            <p class="text-slate-600 dark:text-slate-400">{{ $comment->body }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                @auth
                                    <form method="POST" action="{{ route('projects.comments.store', $project) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="body" required maxlength="1000" placeholder="Сэтгэгдэл бичих..."
                                               class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 dark:placeholder:text-slate-500">
                                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">Илгээх</button>
                                    </form>
                                @endauth
                            </div>
                        </details>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @if ($projects->hasPages())
        <div class="mt-8">{{ $projects->links() }}</div>
    @endif
</div>

@auth
    <div data-modal="create-project" class="fixed inset-0 z-50 {{ $errors->any() ? 'flex' : 'hidden' }} items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:border dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Шинэ төсөл нийтлэх</h2>
                <button type="button" data-modal-close class="text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Гарчиг</label>
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                           class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Тайлбар</label>
                    <textarea name="description" rows="3" maxlength="2000"
                              class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Зураг (хамгийн ихдээ 3)</label>
                    <input type="file" name="images[]" id="project-images" accept="image/*" multiple
                           class="mt-1 w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-slate-200 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700">
                    <div id="project-images-preview" class="mt-3 grid grid-cols-3 gap-2"></div>
                </div>

                @error('images') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                @error('images.*') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                <button type="submit" class="w-full rounded-lg bg-linear-to-r from-indigo-500 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.35)] transition-all duration-300 hover:shadow-[0_0_25px_rgba(99,102,241,0.55)]">
                    Нийтлэх
                </button>
            </form>
        </div>
    </div>
@endauth
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('project-images');
        const preview = document.getElementById('project-images-preview');
        if (!input || !preview) return;

        input.addEventListener('change', () => {
            preview.innerHTML = '';

            let files = Array.from(input.files);
            if (files.length > 3) {
                alert('Хамгийн ихдээ 3 зураг оруулна уу.');
                files = files.slice(0, 3);

                const transfer = new DataTransfer();
                files.forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
            }

            files.forEach((file) => {
                const url = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = url;
                img.className = 'h-20 w-full rounded-lg object-cover';
                img.onload = () => URL.revokeObjectURL(url);
                preview.appendChild(img);
            });
        });
    })();
</script>
@endpush
