<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Services\OutlookCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    /**
     * 施設一覧
     */
    public function index(): Response
    {
        $facilities = Facility::orderBy('id')
            ->withCount('scheduleEvents')
            ->get();

        return Inertia::render('Admin/Facilities/Index', [
            'facilities' => $facilities,
        ]);
    }

    /**
     * 施設登録画面
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Facilities/Create', [
            'outlookRooms' => $this->getOutlookRooms(),
        ]);
    }

    /**
     * 施設を登録
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'outlook_resource_email' => 'nullable|email|max:255',
        ], [], [
            'name' => '施設名',
            'outlook_resource_email' => 'Outlook会議室',
        ]);

        Facility::create($validated);

        return Redirect::route('admin.facilities.index')
            ->with('success', '施設を登録しました。');
    }

    /**
     * 施設編集画面
     */
    public function edit(Facility $facility): Response
    {
        return Inertia::render('Admin/Facilities/Edit', [
            'facility' => $facility,
            'outlookRooms' => $this->getOutlookRooms(),
        ]);
    }

    /**
     * 施設を更新
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'outlook_resource_email' => 'nullable|email|max:255',
        ], [], [
            'name' => '施設名',
            'outlook_resource_email' => 'Outlook会議室',
        ]);

        $facility->update($validated);

        return Redirect::route('admin.facilities.index')
            ->with('success', '施設を更新しました。');
    }

    /**
     * 施設を削除
     */
    public function destroy(Facility $facility)
    {
        // 予約が残っている施設は削除させない（誤削除防止）
        if ($facility->scheduleEvents()->exists()) {
            return Redirect::back()->withErrors([
                'facility' => 'この施設には予約が登録されているため削除できません。先に予約を削除してください。',
            ]);
        }

        $facility->delete();

        return Redirect::route('admin.facilities.index')
            ->with('success', '施設を削除しました。');
    }

    /**
     * Outlookの会議室一覧を取得（紐付け選択用）。
     * 取得できない場合は空配列（手動入力で対応）。
     */
    private function getOutlookRooms(): array
    {
        try {
            $service = app(OutlookCalendarService::class);
            if (!$service->isConfigured()) {
                return [];
            }

            return collect($service->listRooms())
                ->map(fn ($room) => [
                    'displayName' => $room['displayName'] ?? '',
                    'emailAddress' => $room['emailAddress'] ?? '',
                ])
                ->filter(fn ($room) => !empty($room['emailAddress']))
                ->sortBy('displayName')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Outlook会議室一覧の取得に失敗しました', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
