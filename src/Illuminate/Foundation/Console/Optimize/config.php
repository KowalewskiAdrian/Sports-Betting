<?php

$basePath = $app['path.base'];

return array_map('realpath', array(
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Container/Container.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Container/ContextualBindingBuilder.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Foundation/Application.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Bus/Dispatcher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Bus/QueueingDispatcher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Bus/HandlerResolver.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Pipeline/Pipeline.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Support/Renderable.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Support/ResponsePreparer.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Logging/Log.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Exception/Handler.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Config/Repository.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Url/Generator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Events/Dispatcher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Support/Arrayable.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Support/Jsonable.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Cookie/Factory.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Cookie/QueueingFactory.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Encryption/Encrypter.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Queue/QueueableEntity.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/HttpKernel.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Routing/Registrar.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Routing/ResponseFactory.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Routing/UrlGenerator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Routing/UrlRoutable.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Routing/Middleware.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Routing/TerminableMiddleware.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/View/Factory.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Support/MessageProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Support/MessageBag.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/View/View.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Http/Kernel.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Auth/Guard.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Hashing/Hasher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Auth/AuthManager.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Auth/Guard.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Auth/UserProviderInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Auth/EloquentUserProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Container/Container.php',
    $basePath.'/vendor/symfony/http-kernel/Symfony/Component/HttpKernel/HttpKernelInterface.php',
    $basePath.'/vendor/symfony/http-kernel/Symfony/Component/HttpKernel/TerminableInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/EnvironmentDetector.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/ConfigureLogging.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/RegisterFacades.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/RegisterProviders.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/BootProviders.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/LoadConfiguration.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/DetectEnvironment.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Auth/AuthenticatesAndRegistersUsers.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Auth/ResetsPasswords.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Http/Request.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Http/Middleware/FrameGuard.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Request.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/ParameterBag.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/FileBag.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/ServerBag.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/HeaderBag.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session/SessionInterface.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session/SessionBagInterface.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session/Attribute/AttributeBagInterface.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session/Attribute/AttributeBag.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Session/Storage/MetadataBag.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/AcceptHeaderItem.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/AcceptHeader.php',
    $basePath.'/vendor/symfony/debug/Symfony/Component/Debug/ExceptionHandler.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/ServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/AggregateServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/RoutingServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/ControllerServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Events/EventServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Validation/ValidationServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Validation/ValidatesRequests.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Bus/DispatchesCommands.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Providers/FoundationServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Providers/EventServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Providers/FormRequestServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/RouteServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Auth/AuthServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Pagination/PaginationServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Support/Providers/RouteServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/Support/Providers/EventServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Hashing/HashServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Hashing/BcryptHasher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Contracts/Pagination/Paginator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Pagination/AbstractPaginator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Pagination/Paginator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Facades/Facade.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Traits/MacroableTrait.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Arr.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Str.php',
    $basePath.'/vendor/symfony/debug/Symfony/Component/Debug/ErrorHandler.php',
    $basePath.'/vendor/symfony/http-kernel/Symfony/Component/HttpKernel/Debug/ErrorHandler.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Config/Repository.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/NamespacedItemResolver.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Config/FileLoader.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Config/LoaderInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/AliasLoader.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Foundation/ProviderRepository.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Cookie/CookieServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Database/DatabaseServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Encryption/EncryptionServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Session/SessionServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/ViewServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/RouteDependencyResolverTrait.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Router.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Route.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/RouteCollection.php',
    $basePath.'/vendor/symfony/routing/Symfony/Component/Routing/CompiledRoute.php',
    $basePath.'/vendor/symfony/routing/Symfony/Component/Routing/RouteCompilerInterface.php',
    $basePath.'/vendor/symfony/routing/Symfony/Component/Routing/RouteCompiler.php',
    $basePath.'/vendor/symfony/routing/Symfony/Component/Routing/Route.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Controller.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/ControllerInspector.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/UrlGenerator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Bus/BusServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Matching/ValidatorInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Matching/HostValidator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Matching/MethodValidator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Matching/SchemeValidator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Routing/Matching/UriValidator.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Events/Dispatcher.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Database/ConnectionResolverInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Database/Connectors/ConnectionFactory.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Session/SessionInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Session/Store.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Session/SessionManager.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Manager.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Collection.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Cookie/CookieJar.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Encryption/Encrypter.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Facades/Log.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Log/LogServiceProvider.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Log/Writer.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Logger.php',
    $basePath.'/vendor/psr/log/Psr/Log/LoggerInterface.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Handler/AbstractHandler.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Handler/AbstractProcessingHandler.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Handler/StreamHandler.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Handler/RotatingFileHandler.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Handler/HandlerInterface.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Formatter/FormatterInterface.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Formatter/NormalizerFormatter.php',
    $basePath.'/vendor/monolog/monolog/src/Monolog/Formatter/LineFormatter.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Facades/App.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Facades/Route.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Engines/EngineResolver.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/ViewFinderInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/FileViewFinder.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Factory.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/ViewErrorBag.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/MessageBag.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Support/Facades/View.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/View.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Engines/EngineInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Engines/PhpEngine.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Compilers/CompilerInterface.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Compilers/Compiler.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/View/Compilers/BladeCompiler.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Response.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Http/ResponseTrait.php',
    $basePath.'/vendor/laravel/framework/src/Illuminate/Http/Response.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/ResponseHeaderBag.php',
    $basePath.'/vendor/symfony/http-foundation/Symfony/Component/HttpFoundation/Cookie.php',
    $basePath.'/vendor/symfony/security-core/Symfony/Component/Security/Core/Util/StringUtils.php',
    $basePath.'/vendor/symfony/security-core/Symfony/Component/Security/Core/Util/SecureRandomInterface.php',
    $basePath.'/vendor/symfony/security-core/Symfony/Component/Security/Core/Util/SecureRandom.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/SplFileInfo.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Expression/Regex.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Expression/ValueInterface.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Expression/Expression.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Iterator/MultiplePcreFilterIterator.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Iterator/PathFilterIterator.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Iterator/ExcludeDirectoryFilterIterator.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Iterator/RecursiveDirectoryIterator.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Iterator/FilterIterator.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Iterator/FileTypeFilterIterator.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Shell/Shell.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Adapter/AdapterInterface.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Adapter/AbstractAdapter.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Adapter/AbstractFindAdapter.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Adapter/GnuFindAdapter.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Adapter/PhpAdapter.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Adapter/BsdFindAdapter.php',
    $basePath.'/vendor/symfony/finder/Symfony/Component/Finder/Finder.php',
    $basePath.'/vendor/nesbot/carbon/src/Carbon/Carbon.php',
));
