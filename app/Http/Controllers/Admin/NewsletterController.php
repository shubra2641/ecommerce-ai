<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Mail\NewsletterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Newsletter Controller for Admin
 * 
 * Handles newsletter subscription management and email sending
 * for administrators in the admin panel.
 */
class NewsletterController extends Controller
{
    /**
     * Display a listing of newsletter subscribers
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $query = Newsletter::query();

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by email or name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'subscribed_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $subscribers = $query->paginate(20);

            // Get statistics
            $stats = [
                'total' => Newsletter::count(),
                'active' => Newsletter::active()->count(),
                'inactive' => Newsletter::inactive()->count(),
                'unsubscribed' => Newsletter::unsubscribed()->count(),
            ];

            return view('backend.newsletter.index', compact('subscribers', 'stats'));
        } catch (\Exception $e) {
            Log::error('Newsletter index error: ' . $e->getMessage());
            return redirect()->back()->with('error', __('newsletter.load_subscribers_failed'));
        }
    }

    /**
     * Show the form for creating a new newsletter email
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('backend.newsletter.create');
    }

    /**
     * Send newsletter email to subscribers
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'content' => 'required|string',
                'recipients' => 'required|in:all,active,inactive',
                'test_email' => 'nullable|email'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // If test email is provided, send test email
            if ($request->filled('test_email')) {
                $this->sendTestEmail($request->test_email, $request->subject, $request->content);
                return redirect()->back()->with('success', __('newsletter.test_email_sent_successfully'));
            }

            // Get recipients based on selection
            $recipients = $this->getRecipients($request->recipients);

            if ($recipients->isEmpty()) {
                return redirect()->back()->with('error', __('newsletter.no_recipients_found'));
            }

            // Send emails
            $sentCount = 0;
            foreach ($recipients as $subscriber) {
                try {
                    Mail::to($subscriber->email)->send(new NewsletterMail(
                        $request->subject,
                        $request->content,
                        $subscriber
                    ));
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to send newsletter to {$subscriber->email}: " . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', __('newsletter.newsletter_sent_successfully', ['count' => $sentCount]));

        } catch (\Exception $e) {
            Log::error('Newsletter send error: ' . $e->getMessage());
            return redirect()->back()->with('error', __('newsletter.newsletter_send_failed'));
        }
    }

    /**
     * Remove a subscriber from newsletter
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $subscriber = Newsletter::findOrFail($id);
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now()
            ]);

            return redirect()->back()->with('success', __('newsletter.unsubscribed_successfully'));
        } catch (\Exception $e) {
            Log::error('Newsletter destroy error: ' . $e->getMessage());
            return redirect()->back()->with('error', __('newsletter.unsubscription_failed'));
        }
    }

    /**
     * Toggle subscriber status
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        try {
            $subscriber = Newsletter::findOrFail($id);
            
            $newStatus = $subscriber->status === 'active' ? 'inactive' : 'active';
            $subscriber->update([
                'status' => $newStatus,
                'unsubscribed_at' => $newStatus === 'inactive' ? now() : null
            ]);

            $statusText = $newStatus === 'active' ? __('newsletter.active') : __('newsletter.inactive');
            return redirect()->back()->with('success', __('newsletter.status_updated_successfully', ['status' => $statusText]));
        } catch (\Exception $e) {
            Log::error('Newsletter toggle status error: ' . $e->getMessage());
            return redirect()->back()->with('error', __('newsletter.status_update_failed'));
        }
    }

    /**
     * Export subscribers to CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            $query = Newsletter::query();

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $subscribers = $query->orderBy('subscribed_at', 'desc')->get();

            $filename = 'newsletter_subscribers_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($subscribers) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, ['Email', 'Name', 'Status', 'Subscribed At', 'Unsubscribed At']);

                // Add data
                foreach ($subscribers as $subscriber) {
                    fputcsv($file, [
                        $subscriber->email,
                        $subscriber->name,
                        $subscriber->status,
                        $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                        $subscriber->unsubscribed_at?->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Newsletter export error: ' . $e->getMessage());
            return redirect()->back()->with('error', __('newsletter.export_failed'));
        }
    }

    /**
     * Get recipients based on selection
     *
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecipients($type)
    {
        switch ($type) {
            case 'active':
                return Newsletter::active()->get();
            case 'inactive':
                return Newsletter::inactive()->get();
            case 'all':
            default:
                return Newsletter::whereIn('status', ['active', 'inactive'])->get();
        }
    }

    /**
     * Send test email
     *
     * @param string $email
     * @param string $subject
     * @param string $content
     * @return void
     */
    private function sendTestEmail($email, $subject, $content)
    {
        $testSubscriber = new Newsletter([
            'email' => $email,
            'name' => 'Test User',
            'unsubscribe_token' => 'test-token'
        ]);

        Mail::to($email)->send(new NewsletterMail($subject, $content, $testSubscriber));
    }
}
