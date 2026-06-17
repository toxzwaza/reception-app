<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // クロージャで遅延評価する（localstorage.auth ミドルウェアの setUserResolver は
            // このミドルウェアの後に実行されるため、即値だと user が null になる）。
            // フォールバックとしてセッションの localStorage_user_id からも解決する。
            'auth' => [
                'user' => fn () => $request->user()
                    ?? ($request->session()->get('localStorage_user_id')
                        ? \App\Models\User::where('id', $request->session()->get('localStorage_user_id'))
                            ->where('del_flg', 0)
                            ->first()
                        : null),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
