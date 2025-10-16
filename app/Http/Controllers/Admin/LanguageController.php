<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Language;
use Exception;
use App\Http\Requests\Admin\LanguageStoreRequest;
use App\Http\Requests\Admin\LanguageStoreRequest as LanguageUpdateRequest;

/**
 * LanguageController handles language management operations
 * 
 * This controller manages language creation, editing, deletion, file management,
 * and language settings with secure validation and proper error handling.
 */
class LanguageController extends Controller
{
    /**
     * Display a listing of all languages in admin panel
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            // Get all languages ordered by sort_order field
            $languages = Language::orderBy('sort_order')->get();
            
            return view('backend.language.index', compact('languages'));
            
        } catch (Exception $e) {
            \Log::error('Error loading languages: ' . $e->getMessage());
            return view('backend.language.index', ['languages' => collect()]);
        }
    }

    /**
     * Show the form for creating a new language
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.language.create');
        } catch (Exception $e) {
            \Log::error('Error loading create language form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created language in database
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(LanguageStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['name', 'code', 'flag', 'direction', 'is_default', 'is_active', 'sort_order'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));
            
            // Convert checkbox values to boolean
            $data['is_default'] = $request->has('is_default');
            $data['is_active'] = $request->has('is_active');

            // If this language is set as default, remove default status from other languages
            if ($data['is_default']) {
                Language::where('is_default', true)->update(['is_default' => false]);
            }

            // Create the new language record
            $language = Language::create($data);

            // Create language files for the new language
            $this->createLanguageFile($language->code);

            request()->session()->flash('success', 'Language created successfully');
            
        } catch (Exception $e) {
            \Log::error('Error creating language: ' . $e->getMessage(), [
                'request_data' => $request->only(['name', 'code', 'direction'])
            ]);
            request()->session()->flash('error', 'An error occurred while creating the language');
        }

        return redirect()->route('language.index');
    }

    /**
     * Display details of a specific language
     * 
     * @param string $id
     * @return View
     */
    public function show(string $id): View
    {
        try {
            $language = Language::findOrFail($id);
            
            // Prepare list of expected language files and the language path for the view
            $langPath = resource_path('lang/' . $language->code);
            $files = ['app.php', 'auth.php', 'pagination.php', 'passwords.php', 'validation.php'];

            return view('backend.language.show', compact('language', 'langPath', 'files'));
            
        } catch (Exception $e) {
            \Log::error('Error loading language details: ' . $e->getMessage(), [
                'language_id' => $id
            ]);
            abort(404, 'Language not found');
        }
    }

    /**
     * Show the form for editing an existing language
     * 
     * @param string $id
     * @return View
     */
    public function edit(string $id): View
    {
        try {
            $language = Language::findOrFail($id);
            return view('backend.language.edit', compact('language'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit language form: ' . $e->getMessage(), [
                'language_id' => $id
            ]);
            abort(404, 'Language not found');
        }
    }

    /**
     * Update an existing language in database
     * 
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(LanguageUpdateRequest $request, string $id): RedirectResponse
    {
        try {
            $language = Language::findOrFail($id);
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['name', 'code', 'flag', 'direction', 'is_default', 'is_active', 'sort_order'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));
            
            // Convert checkbox values to boolean
            $data['is_default'] = $request->has('is_default');
            $data['is_active'] = $request->has('is_active');

            // If this language is set as default, remove default status from other languages
            if ($data['is_default']) {
                Language::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
            }

            // Update the language record with new data
            $language->update($data);

            request()->session()->flash('success', 'Language updated successfully');
            
        } catch (Exception $e) {
            \Log::error('Error updating language: ' . $e->getMessage(), [
                'language_id' => $id,
                'request_data' => $request->only(['name', 'code', 'direction'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the language');
        }

        return redirect()->route('language.index');
    }

    /**
     * Delete a language from database and file system
     * 
     * @param string $id
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $language = Language::findOrFail($id);
            
            // Prevent deletion of default language to maintain system stability
            if ($language->is_default) {
                request()->session()->flash('error', 'Cannot delete default language');
                return redirect()->route('language.index');
            }

            // Delete language files from file system
            $this->deleteLanguageFile($language->code);
            
            // Delete the language record from database
            $language->delete();
            
            request()->session()->flash('success', 'Language deleted successfully');
            
        } catch (Exception $e) {
            \Log::error('Error deleting language: ' . $e->getMessage(), [
                'language_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the language');
        }

        return redirect()->route('language.index');
    }

    /**
     * Create language files for a new language
     * 
     * @param string $code
     * @return void
     * @throws Exception
     */
    private function createLanguageFile(string $code): void
    {
        try {
            // Validate language code
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $code)) {
                throw new Exception('Invalid language code format');
            }

            // Define the language directory path
            $langPath = resource_path('lang/' . $code);
            
            // Create language directory if it doesn't exist
            if (!File::exists($langPath)) {
                File::makeDirectory($langPath, 0755, true);
            }

            // Define basic language files that should be created
            $files = ['app.php', 'auth.php', 'pagination.php', 'passwords.php', 'validation.php'];
            
            // Create each language file if it doesn't exist
            foreach ($files as $file) {
                $filePath = $langPath . '/' . $file;
                if (!File::exists($filePath)) {
                    // Create file with basic PHP array structure
                    $content = "<?php\n\nreturn [\n    // Add your translations here\n];";
                    File::put($filePath, $content);
                }
            }
            
        } catch (Exception $e) {
            \Log::error('Error creating language files: ' . $e->getMessage(), [
                'language_code' => $code
            ]);
            throw $e;
        }
    }

    /**
     * Delete language files from file system
     * 
     * @param string $code
     * @return void
     * @throws Exception
     */
    private function deleteLanguageFile(string $code): void
    {
        try {
            // Validate language code
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $code)) {
                throw new Exception('Invalid language code format');
            }

            // Define the language directory path
            $langPath = resource_path('lang/' . $code);
            
            // Delete the entire language directory if it exists
            if (File::exists($langPath)) {
                File::deleteDirectory($langPath);
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting language files: ' . $e->getMessage(), [
                'language_code' => $code
            ]);
            throw $e;
        }
    }

    /**
     * Set a language as the default language
     * 
     * @param string $id
     * @return RedirectResponse
     */
    public function setDefault(string $id): RedirectResponse
    {
        try {
            $language = Language::findOrFail($id);
            
            // Use the model method to set this language as default
            $language->setAsDefault();
            
            request()->session()->flash('success', 'Default language updated successfully');
            
        } catch (Exception $e) {
            \Log::error('Error setting default language: ' . $e->getMessage(), [
                'language_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while setting default language');
        }

        return redirect()->route('language.index');
    }

    /**
     * Toggle language active status
     * 
     * @param string $id
     * @return RedirectResponse
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        try {
            $language = Language::findOrFail($id);
            
            // Toggle the active status (true becomes false, false becomes true)
            $language->update(['is_active' => !$language->is_active]);
            
            // Determine the status message based on new state
            $status = $language->is_active ? 'activated' : 'deactivated';
            
            request()->session()->flash('success', "Language {$status} successfully");
            
        } catch (Exception $e) {
            \Log::error('Error toggling language status: ' . $e->getMessage(), [
                'language_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while updating language status');
        }

        return redirect()->route('language.index');
    }

    /**
     * Show language file editor
     * 
     * @param string $id
     * @param string $filename
     * @return View|RedirectResponse
     */
    public function editFile(string $id, string $filename)
    {
        try {
            $language = Language::findOrFail($id);
            
            // Validate filename to prevent directory traversal
            if (!$this->isValidLanguageFilename($filename)) {
                request()->session()->flash('error', 'Invalid filename');
                return redirect()->route('language.show', $id);
            }
            
            // Define the language file path
            $filePath = resource_path('lang/' . $language->code . '/' . $filename);
            
            // Check if file exists
            if (!File::exists($filePath)) {
                request()->session()->flash('error', 'Language file not found');
                return redirect()->route('language.show', $id);
            }
            
            // Read file content
            $content = File::get($filePath);
            
            return view('backend.language.edit-file', compact('language', 'filename', 'content'));
            
        } catch (Exception $e) {
            \Log::error('Error loading language file editor: ' . $e->getMessage(), [
                'language_id' => $id,
                'filename' => $filename
            ]);
            request()->session()->flash('error', 'An error occurred while loading the file editor');
            return redirect()->route('language.show', $id);
        }
    }

    /**
     * Update language file content
     * 
     * @param Request $request
     * @param string $id
     * @param string $filename
     * @return RedirectResponse
     */
    public function updateFile(\App\Http\Requests\Admin\LanguageUpdateFileRequest $request, string $id, string $filename): RedirectResponse
    {
        try {
            $language = Language::findOrFail($id);
            
            // Validate filename to prevent directory traversal
            if (!$this->isValidLanguageFilename($filename)) {
                request()->session()->flash('error', 'Invalid filename');
                return redirect()->route('language.show', $id);
            }
            
            // Use validated data from FormRequest
            $validatedData = $request->validated();
            
            // Define the language file path
            $filePath = resource_path('lang/' . $language->code . '/' . $filename);
            
            // Check if file exists
            if (!File::exists($filePath)) {
                request()->session()->flash('error', 'Language file not found');
                return redirect()->route('language.show', $id);
            }
            
            // Validate PHP syntax before saving
            if (!$this->isValidPhpSyntax($validatedData['content'])) {
                request()->session()->flash('error', 'Invalid PHP syntax in file content');
                return redirect()->route('language.editFile', [$id, $filename]);
            }
            
            // Write the new content to the file
            File::put($filePath, $validatedData['content']);
            
            request()->session()->flash('success', 'Language file updated successfully');
            
        } catch (Exception $e) {
            \Log::error('Error updating language file: ' . $e->getMessage(), [
                'language_id' => $id,
                'filename' => $filename
            ]);
            request()->session()->flash('error', 'Failed to update language file: ' . $e->getMessage());
        }

        return redirect()->route('language.show', $id);
    }

    /**
     * Create missing language file
     * 
     * @param string $id
     * @param string $filename
     * @return RedirectResponse
     */
    public function createFile(string $id, string $filename): RedirectResponse
    {
        try {
            $language = Language::findOrFail($id);
            
            // Validate filename to prevent directory traversal
            if (!$this->isValidLanguageFilename($filename)) {
                request()->session()->flash('error', 'Invalid filename');
                return redirect()->route('language.show', $id);
            }
            
            // Define the language file path
            $filePath = resource_path('lang/' . $language->code . '/' . $filename);
            
            // Check if file already exists
            if (File::exists($filePath)) {
                request()->session()->flash('warning', 'Language file already exists');
                return redirect()->route('language.show', $id);
            }
            
            // Create basic file content
            $content = "<?php\n\nreturn [\n    // Add your translations here\n];";
            
            // Write the content to the file
            File::put($filePath, $content);
            
            request()->session()->flash('success', 'Language file created successfully');
            
        } catch (Exception $e) {
            \Log::error('Error creating language file: ' . $e->getMessage(), [
                'language_id' => $id,
                'filename' => $filename
            ]);
            request()->session()->flash('error', 'Failed to create language file: ' . $e->getMessage());
        }

        return redirect()->route('language.show', $id);
    }

    /**
     * Validate language filename to prevent directory traversal attacks
     * 
     * @param string $filename
     * @return bool
     */
    private function isValidLanguageFilename(string $filename): bool
    {
        // Only allow alphanumeric characters, dots, hyphens, and underscores
        // Must end with .php extension
        return preg_match('/^[a-zA-Z0-9._-]+\.php$/', $filename) && 
               !str_contains($filename, '..') &&
               strlen($filename) <= 255;
    }

    /**
     * Validate PHP syntax for language file content
     * 
     * @param string $content
     * @return bool
     */
    private function isValidPhpSyntax(string $content): bool
    {
        // Check if content starts with PHP opening tag
        if (!str_starts_with(trim($content), '<?php')) {
            return false;
        }

        // Use PHP's syntax checking
        $tempFile = tempnam(sys_get_temp_dir(), 'lang_check_');
        file_put_contents($tempFile, $content);
        
        $output = [];
        $returnCode = 0;
        exec("php -l {$tempFile} 2>&1", $output, $returnCode);
        
        unlink($tempFile);
        
        return $returnCode === 0;
    }

    /**
     * Switch application language
     * 
     * @param string $code Language code
     * @return RedirectResponse
     */
    public function switchLanguage(string $code): RedirectResponse
    {
        try {
            // Validate language code
            $language = Language::getByCode($code);
            
            if (!$language || !$language->is_active) {
                return redirect()->back()->with('error', 'Invalid or inactive language selected.');
            }
            
            // Set language in session
            session(['locale' => $code, 'text_direction' => $language->direction]);
            
            return redirect()->back()->with('success', 'Language switched successfully.');
            
        } catch (Exception $e) {
            \Log::error('Error switching language: ' . $e->getMessage(), [
                'language_code' => $code,
                'ip' => request()->ip()
            ]);
            
            return redirect()->back()->with('error', 'Error switching language. Please try again.');
        }
    }
}