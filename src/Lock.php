<?php

namespace Lysice\HyperfRedisLock;

use Closure;
use Hyperf\Stringable\Str;
use Hyperf\Support\Traits\InteractsWithTime;

abstract class Lock implements LockContract
{
    use InteractsWithTime;

    /**
     * The name of the lock
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $seconds;

    /**
     * The scope identifier of this lock
     * @var string
     */
    protected mixed $owner;

    public function __construct($name, $seconds, $owner = null)
    {
        $this->name = $name;
        $this->seconds = $seconds;
        $this->owner = $owner ?: Str::random();
    }

    /**
     * Attempt to acquire the lock
     * @return bool
     */
    abstract public function acquire(): bool;

    /**
     * Release the lock
     * @return bool
     */
    abstract public function release(): bool;

    /**
     * Returns the owner value written into the driver for this lock
     * @return string
     */
    abstract protected function getCurrentOwner(): string;

    /**
     * Attempt to acquire the lock
     * @param callable|null $callback
     * @param null|\Closure $finally
     * @return bool|mixed
     */
    public function get(mixed $callback = null, mixed $finally = null): mixed
    {
        $result = $this->acquire();
        if($result && is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }
        if (!$result && is_callable($finally)) {
            return $finally();
        }

        return $result;
    }

    /**
     * @param $seconds
     * @param null $callback
     * @return bool|mixed
     * @throws LockTimeoutException
     */
    public function block($seconds, $callback = null): mixed
    {
        $starting = $this->currentTime();
        while(! $this->acquire()) {
           usleep(250 * 1000);
           if($this->currentTime() - $seconds >= $starting) {
               throw new LockTimeoutException();
           }
        }

        if(is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }

        return true;
    }

    /**
     * Returns the current owner of the lock.
     *
     * @return string
     */
    public function owner(): string
    {
        return $this->owner;
    }

    /**
     * Determines whether this lock is allowed to release the lock in the driver.
     *
     * @return bool
     */
    protected function isOwnedByCurrentProcess(): bool
    {
        return $this->getCurrentOwner() === $this->owner;
    }
}
