<?php

declare(strict_types=1);

namespace MichaelRubel\AutoBinder;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MichaelRubel\AutoBinder\Traits\AutoBindsToContainer;

class AutoBinder
{
    use AutoBindsToContainer;

    /**
     * Base class namespace.
     *
     * @var string
     */
    public string $classNamespace = 'App';

    /**
     * Target class folder.
     *
     * @var string
     */
    public string $classFolder = 'Services';

    /**
     * Interface namespace (can be fully qualified).
     *
     * @var string
     */
    public string $interfaceNamingspace = 'Interfaces';

    /**
     * Postfix convention for interfaces.
     *
     * @var string
     */
    public string $interfaceNaming = 'Interface';

    /**
     * Base class folder.
     *
     * @var string
     */
    public string $basePath = 'app';

    /**
     * The type of bindings.
     *
     * @var string
     */
    public string $bindingType = 'singleton';

    /**
     * When the class name is met, these dependencies are passed to the concrete.
     *
     * @var array
     */
    public array $dependencies = [];

    /**
     * Assign a new class folder.
     *
     * @param string|null $classFolder
     */
    final public function __construct(?string $classFolder = null)
    {
        if ($classFolder) {
            $this->classFolder = $classFolder;
        }
    }

    /**
     * Create the object with target folder assigned.
     *
     * @param string|array $folder
     *
     * @return static|Collection
     */
    public static function from(string|array $folder): static|Collection
    {
        $folder  = is_string($folder) ? $folder : current($folder);
        $folders = collect(is_array($folder) ? $folder : func_get_args());

        return func_num_args() > 1
            ? $folders->map(fn ($folder) => new static($folder))
            : new static($folder);
    }

    /**
     * Set the class base path.
     *
     * @param string $basePath
     *
     * @return $this
     */
    public function basePath(string $basePath): static
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Define the class namespace.
     *
     * @param string $path
     *
     * @return $this
     */
    public function classNamespace(string $path): static
    {
        $this->classNamespace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Define the interface namespace.
     *
     * @param string $path
     *
     * @return $this
     */
    public function interfaceNamingspace(string $path): static
    {
        $this->interfaceNamingspace = $this->namespaceFrom($path);

        return $this;
    }

    /**
     * Define the interface postfix.
     *
     * @param string $name
     *
     * @return $this
     */
    public function interfaceNaming(string $name): static
    {
        $this->interfaceNaming = Str::ucfirst($name);

        return $this;
    }

    /**
     * Adds dependencies to the class when the class name is met.
     *
     * @param string   $abstract
     * @param \Closure $callback
     *
     * @return static
     */
    public function when(string $abstract, \Closure $callback): static
    {
        $this->dependencies[$abstract] = $callback;

        return $this;
    }

    /**
     * Bind the result as a specific type of binding.
     *
     * @param string $bindingType
     *
     * @return $this
     */
    public function as(string $bindingType): static
    {
        if (! Str::is(['bind', 'scoped', 'singleton'], $bindingType)) {
            throw new \InvalidArgumentException('Invalid binding type.');
        }

        $this->bindingType = $bindingType;

        return $this;
    }

    /**
     * Perform the bindings.
     *
     * @return void
     */
    public function bind(): void
    {
        $this->run();
    }
}
