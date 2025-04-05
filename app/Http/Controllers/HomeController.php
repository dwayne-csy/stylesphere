<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    const PAGINATION_LENGTH = 12;

    public function index(): View
    {
        $products = Product::with(['supplier', 'images', 'reviews.user'])
            ->when(request('price_range'), function($query) {
                $range = explode('-', request('price_range'));
                if (count($range) === 2) {
                    $min = (float)trim($range[0]);
                    $max = (float)trim($range[1]);
                    $query->whereBetween('sell_price', [$min, $max]);
                }
            })
            ->when(request('sort') == 'price_asc', function($query) {
                $query->orderBy('sell_price', 'asc');
            })
            ->when(request('sort') == 'price_desc', function($query) {
                $query->orderBy('sell_price', 'desc');
            })
            ->when(!request('sort'), function($query) {
                $query->latest();
            })
            ->paginate(self::PAGINATION_LENGTH);

        return view('home', compact('products'));
    }

    public function search(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'price_range' => 'nullable|string|regex:/^\d+-\d+$/'
        ]);

        $searchTerm = $validated['search'] ?? '';

        if(empty(trim($searchTerm))) {
            return redirect()->route('home');
        }

        $products = Product::with(['supplier', 'images', 'reviews.user'])
            ->where(function($query) use ($searchTerm) {
                $query->where('product_name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            })
            ->when($validated['price_range'] ?? null, function($query) use ($validated) {
                $range = explode('-', $validated['price_range']);
                if (count($range) === 2) {
                    $min = (float)trim($range[0]);
                    $max = (float)trim($range[1]);
                    $query->whereBetween('sell_price', [$min, $max]);
                }
            })
            ->when(request('sort') == 'price_asc', function($query) {
                $query->orderBy('sell_price', 'asc');
            })
            ->when(request('sort') == 'price_desc', function($query) {
                $query->orderBy('sell_price', 'desc');
            })
            ->when(!request('sort'), function($query) {
                $query->latest();
            })
            ->paginate(self::PAGINATION_LENGTH);

        return view('home', [
            'products' => $products,
            'searchTerm' => $searchTerm
        ]);
    }
}