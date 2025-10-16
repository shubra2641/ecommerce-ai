<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\Frontend\CompareAddRequest;
use App\Http\Requests\Frontend\CompareRemoveRequest;
use App\Models\Compare;
use App\Models\Product;
use Exception;

class CompareController extends Controller
{
    /**
     * Show compare list
     */
    public function index(): View
    {
        $compares = Compare::with('product')->mine()->latest()->get();
        // Enhance product photos if available
        $compares = $compares->map(function($c){
            if ($c->product && $c->product->photo) {
                $c->product->photo_array = explode(',', $c->product->photo);
            }
            return $c;
        });
        return view('frontend.pages.compare', compact('compares'));
    }

    /**
     * Add product to compare
     */
    public function add(CompareAddRequest $request, string $slug): RedirectResponse
    {
        try {
            $product = Product::where('slug', $slug)->first();
            if (!$product) {
                session()->flash('error', 'Product not found');
                return back();
            }

            // Enforce max items (e.g., 4)
            $count = Compare::mine()->count();
            if ($count >= 4) {
                session()->flash('error', 'You can compare up to 4 products only');
                return back();
            }

            // Duplicate check
            $exists = Compare::mine()->where('product_id', $product->id)->first();
            if ($exists) {
                session()->flash('error', 'Product already in compare list');
                return back();
            }

            Compare::create([
                'user_id' => Auth::id(),
                'session_id' => Auth::check() ? null : session()->getId(),
                'product_id' => $product->id,
                'price' => $product->price,
                'compare_at_price' => $product->price - (($product->discount ?? 0) * $product->price / 100)
            ]);

            session()->flash('success', 'Product added to compare');
        } catch (\Illuminate\Database\QueryException $qe) {
            $sqlState = $qe->errorInfo[0] ?? null;
            $code = $qe->errorInfo[1] ?? null;
            if (str_contains(strtolower($qe->getMessage()), 'compares')) {
                session()->flash('error', 'Compare table not migrated. Run migrations.');
            } else {
                session()->flash('error', 'Database error while adding to compare');
            }
            \Log::error('Compare add query error: '.$qe->getMessage(), [
                'slug' => $slug,
                'sql_state' => $sqlState,
                'code' => $code,
                'user_id' => Auth::id()
            ]);
        } catch (Exception $e) {
            \Log::error('Compare add failed: '.$e->getMessage(), [
                'slug' => $slug,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Unable to add to compare');
        }
        return back();
    }

    /**
     * Remove a compare item
     */
    public function remove(CompareRemoveRequest $request, int $id): RedirectResponse
    {
        $item = Compare::mine()->where('id', $id)->first();
        if ($item) {
            $item->delete();
            session()->flash('success', 'Removed from compare');
        } else {
            session()->flash('error', 'Item not found');
        }
        return back();
    }

    /**
     * Clear all compare items
     */
    public function clear(): RedirectResponse
    {
        Compare::mine()->delete();
        session()->flash('success', 'Compare list cleared');
        return back();
    }
}
