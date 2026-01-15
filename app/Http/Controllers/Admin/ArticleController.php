<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Journal;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with(['journal', 'creator'])
            ->latest()
            ->paginate(20);
        
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $journals = Journal::orderBy('title')->get();
        return view('admin.articles.create', compact('journals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'article_number' => 'required|string|max:255|unique:articles',
            'title' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'author_phone' => 'nullable|string|max:255',
            'author_username' => 'nullable|string|max:255',
            'author_password' => 'nullable|string|max:255',
            'submit_link' => 'nullable|url',
            'turnitin_link' => 'nullable|url',
            'loa_link' => 'nullable|url',
            'copyediting_link' => 'nullable|url',
            'publication_link' => 'nullable|url',
            'marketing' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'editor1' => 'nullable|string|max:255',
            'pic_editor1' => 'nullable|string|max:255',
            'author1' => 'nullable|string|max:255',
            'pic_author1' => 'nullable|string|max:255',
            'editor2' => 'nullable|string|max:255',
            'pic_editor2' => 'nullable|string|max:255',
            'reviewer1' => 'nullable|string|max:255',
            'pic_reviewer1' => 'nullable|string|max:255',
            'reviewer2' => 'nullable|string|max:255',
            'pic_reviewer2' => 'nullable|string|max:255',
            'pic_copyediting' => 'nullable|string|max:255',
            'pic_production' => 'nullable|string|max:255',
            'status' => 'required|in:SUBMITTED,REVIEW,REVISION,COPYEDITING,PRODUCTION,PUBLISHED,REJECTED',
            'submission_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'revision_date' => 'nullable|date',
            'acceptance_date' => 'nullable|date',
            'publication_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        Article::create($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil ditambahkan');
    }

    public function show(Article $article)
    {
        $article->load(['journal', 'creator']);
        return view('admin.articles.show', compact('article'));
    }

    public function edit(Article $article)
    {
        $journals = Journal::orderBy('title')->get();
        return view('admin.articles.edit', compact('article', 'journals'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'article_number' => 'required|string|max:255|unique:articles,article_number,' . $article->id,
            'title' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'author_phone' => 'nullable|string|max:255',
            'author_username' => 'nullable|string|max:255',
            'author_password' => 'nullable|string|max:255',
            'submit_link' => 'nullable|url',
            'turnitin_link' => 'nullable|url',
            'loa_link' => 'nullable|url',
            'copyediting_link' => 'nullable|url',
            'publication_link' => 'nullable|url',
            'marketing' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'editor1' => 'nullable|string|max:255',
            'pic_editor1' => 'nullable|string|max:255',
            'author1' => 'nullable|string|max:255',
            'pic_author1' => 'nullable|string|max:255',
            'editor2' => 'nullable|string|max:255',
            'pic_editor2' => 'nullable|string|max:255',
            'reviewer1' => 'nullable|string|max:255',
            'pic_reviewer1' => 'nullable|string|max:255',
            'reviewer2' => 'nullable|string|max:255',
            'pic_reviewer2' => 'nullable|string|max:255',
            'pic_copyediting' => 'nullable|string|max:255',
            'pic_production' => 'nullable|string|max:255',
            'status' => 'required|in:SUBMITTED,REVIEW,REVISION,COPYEDITING,PRODUCTION,PUBLISHED,REJECTED',
            'submission_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'revision_date' => 'nullable|date',
            'acceptance_date' => 'nullable|date',
            'publication_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $article->update($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil diupdate');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil dihapus');
    }
}
