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

    public function monitoring()
    {
        $articles = Article::with(['journal', 'creator'])
            ->latest()
            ->get();
        
        return view('admin.articles.monitoring', compact('articles'));
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

    // Workflow Stage Methods
    public function updateSubmission(Request $request, Article $article)
    {
        $validated = $request->validate([
            'submission_date' => 'nullable|date',
            'submit_link' => 'nullable|url',
            'submission_completed' => 'boolean',
            'submission_comment' => 'nullable|string',
        ]);

        $article->update($validated);

        return back()->with('success', 'Submission stage berhasil disimpan');
    }

    public function updateReview(Request $request, Article $article)
    {
        $validated = $request->validate([
            'reviewer1' => 'nullable|string|max:255',
            'pic_reviewer1' => 'nullable|string|max:255',
            'reviewer2' => 'nullable|string|max:255',
            'pic_reviewer2' => 'nullable|string|max:255',
            'review_start_date' => 'nullable|date',
            'review_end_date' => 'nullable|date',
            'review_completed' => 'boolean',
            'review_comment' => 'nullable|string',
        ]);

        $article->update($validated);

        return back()->with('success', 'Review stage berhasil disimpan');
    }

    public function updateRevision(Request $request, Article $article)
    {
        $validated = $request->validate([
            'editor1' => 'nullable|string|max:255',
            'pic_editor1' => 'nullable|string|max:255',
            'revision_start_date' => 'nullable|date',
            'revision_end_date' => 'nullable|date',
            'revision_completed' => 'boolean',
            'revision_comment' => 'nullable|string',
        ]);

        $article->update($validated);

        return back()->with('success', 'Revision stage berhasil disimpan');
    }

    public function updateAcceptance(Request $request, Article $article)
    {
        $validated = $request->validate([
            'acceptance_date' => 'nullable|date',
            'loa_link' => 'nullable|url',
            'acceptance_completed' => 'boolean',
            'acceptance_comment' => 'nullable|string',
        ]);

        $article->update($validated);

        return back()->with('success', 'Acceptance stage berhasil disimpan');
    }

    public function updateCopyediting(Request $request, Article $article)
    {
        $validated = $request->validate([
            'editor2' => 'nullable|string|max:255',
            'pic_editor2' => 'nullable|string|max:255',
            'author1' => 'nullable|string|max:255',
            'pic_author1' => 'nullable|string|max:255',
            'copyediting_start_date' => 'nullable|date',
            'copyediting_end_date' => 'nullable|date',
            'copyediting_link' => 'nullable|url',
            'copyediting_completed' => 'boolean',
            'copyediting_comment' => 'nullable|string',
        ]);

        $article->update($validated);

        return back()->with('success', 'Copyediting stage berhasil disimpan');
    }

    public function updateProduction(Request $request, Article $article)
    {
        $validated = $request->validate([
            'turnitin_link' => 'nullable|url',
            'production_start_date' => 'nullable|date',
            'production_end_date' => 'nullable|date',
            'production_completed' => 'boolean',
            'production_comment' => 'nullable|string',
        ]);

        $article->update($validated);

        return back()->with('success', 'Production stage berhasil disimpan');
    }

    public function updatePublication(Request $request, Article $article)
    {
        $validated = $request->validate([
            'publication_date' => 'nullable|date',
            'publication_link' => 'nullable|url',
            'publication_completed' => 'boolean',
            'publication_comment' => 'nullable|string',
        ]);

        $article->update($validated);

        return back()->with('success', 'Publication stage berhasil disimpan');
    }
}
