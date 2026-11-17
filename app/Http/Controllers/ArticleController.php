<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    /**
     * Display the B2B Product Catalog with Search, Category Filters & Dynamic Sorting.
     */
    public function index(Request $request)
    {
        $query = Article::query();

        // 1. Unified Search by SKU (Ref), Designation, or Description
        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('ref', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Filter by Category
        if ($request->filled('category')) {
            $query->where('erp_category', $request->input('category'));
        }

        // 3. In-Stock Only Toggle
        if ($request->boolean('in_stock')) {
            $query->where('qte_stock', '>', 0);
        }

        // 4. Dynamic Sorting
        $sort = $request->input('sort', 'ref_asc');
        match ($sort) {
            'price_asc'   => $query->orderBy('prix', 'asc'),
            'price_desc'  => $query->orderBy('prix', 'desc'),
            'stock_desc'  => $query->orderBy('qte_stock', 'desc'),
            'name_asc'    => $query->orderBy('designation', 'asc'),
            default       => $query->orderBy('ref', 'asc'),
        };

        // 24 items per page for clean grid & table rendering
        $articles = $query->paginate(24)->withQueryString();

        // Distinct category list for the sidebar filter
        $categories = Article::whereNotNull('erp_category')
            ->select('erp_category')
            ->distinct()
            ->orderBy('erp_category')
            ->pluck('erp_category');

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    /**
     * Add a new article manually.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ref'         => 'required|unique:articles,ref',
            'designation' => 'required',
            'stock'       => 'required|integer',
            'instance'    => 'required|integer',
            'reserve'     => 'required|integer',
            'prix'        => 'required|numeric',
            'description' => 'nullable',
        ]);

        $article = new Article();
        $article->ref          = $request->ref;
        $article->designation  = $request->designation;
        $article->qte_stock    = $request->stock;
        $article->qte_instance = $request->instance;
        $article->qte_reserve  = $request->reserve;
        $article->prix         = $request->prix;
        $article->description  = $request->description ?: $request->designation;

        if ($article->save()) {
            return redirect()->route('articles')->with('success', 'Article créé avec succès.');
        }

        return back()->with('error', 'Échec de la création de l\'article.');
    }

    /**
     * Update an existing article.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id_article'  => 'required|exists:articles,id',
            'ref'         => 'required',
            'designation' => 'required',
            'stock'       => 'required|integer',
            'instance'    => 'required|integer',
            'reserve'     => 'required|integer',
            'prix'        => 'required|numeric',
            'description' => 'nullable',
        ]);

        $article = Article::findOrFail($request->id_article);
        $article->ref          = $request->ref;
        $article->designation  = $request->designation;
        $article->qte_stock    = $request->stock;
        $article->qte_instance = $request->instance;
        $article->qte_reserve  = $request->reserve;
        $article->prix         = $request->prix;
        $article->description  = $request->description ?: $request->designation;

        if ($article->save()) {
            return redirect()->route('articles')->with('success', 'Article mis à jour avec succès.');
        }

        return back()->with('error', 'Échec de la mise à jour.');
    }

    /**
     * Delete an article.
     */
    public function destroy($id)
    {
        $article = Article::find($id);
        if ($article && $article->delete()) {
            return redirect()->back()->with('success', 'Article supprimé avec succès.');
        }
        return redirect()->back()->with('error', 'Erreur lors de la suppression.');
    }

    /**
     * Toggle featured status for B2B homepage.
     */
    public function updateSel(Request $request)
    {
        $data = $request->validate([
            'id'  => 'required|integer',
            'sel' => 'required|boolean',
        ]);

        $item = Article::find($data['id']);
        if ($item) {
            // `sel` is ENUM('0','1'): it must get the STRING '0'/'1'. An integer is read by
            // MySQL as an enum *position* (1 → first value '0'), so ticking never saved.
            $item->sel = $request->boolean('sel') ? '1' : '0';
            $item->save();

            return response()->json(['success' => true, 'sel' => $item->sel]);
        }

        return response()->json(['success' => false, 'message' => 'Article introuvable'], 404);
    }

    /**
     * Welcome page featured items.
     */
    public function examples()
    {
        $products = Article::where('sel', '1')->paginate(12);
        return view('welcome', compact('products'));
    }
}