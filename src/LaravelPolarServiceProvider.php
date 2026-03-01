<?php

namespace Climactic\LaravelPolar;

use Climactic\LaravelPolar\Commands\ListProductsCommand;
use Climactic\LaravelPolar\Http\Middleware\Subscribed;
use Climactic\LaravelPolar\View\Components\Button;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelPolarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-polar')
            ->hasConfigFile(["polar", "webhook-client"])
            ->hasViews()
            ->hasViewComponent('polar', Button::class)
            ->hasMigrations()
            ->discoversMigrations()
            ->hasRoute("web")
            ->hasCommands(
                ListProductsCommand::class,
            )
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('climactic/laravel-polar');
            });
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(\Polar\Polar::class, function () {
            return LaravelPolar::sdk();
        });

        $this->app->alias(\Polar\Polar::class, 'polar.sdk');
    }

    public function boot(): void
    {
        parent::boot();

        $this->bootDirectives();
        $this->bootMiddleware();
    }

    protected function bootDirectives(): void
    {
        Blade::directive('polarEmbedScript', function () {
            return "<?php echo view('polar::js'); ?>";
        });

        Blade::if('subscribed', function ($billableOrType = 'default', ?string $typeOrProduct = null, ?string $productId = null) {
            if ($billableOrType instanceof Model) {
                $billable = $billableOrType;
                $type = $typeOrProduct ?? 'default';
            } else {
                $billable = auth()->user();
                $type = $billableOrType;
                $productId = $typeOrProduct;
            }

            return $billable && method_exists($billable, 'subscribed') && $billable->subscribed($type, $productId);
        });

        Blade::if('onTrial', function ($billableOrType = 'default', ?string $type = null) {
            if ($billableOrType instanceof Model) {
                $billable = $billableOrType;
                $subscriptionType = $type ?? 'default';
            } else {
                $billable = auth()->user();
                $subscriptionType = $billableOrType;
            }

            if (! $billable || ! method_exists($billable, 'subscription')) {
                return false;
            }

            $subscription = $billable->subscription($subscriptionType);

            return $subscription && $subscription->onTrial();
        });
    }

    protected function bootMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('polar.subscribed', Subscribed::class);
    }
}
