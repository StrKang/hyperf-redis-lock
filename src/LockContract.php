<?php

namespace Lysice\HyperfRedisLock;

interface LockContract {
    /**
     * Attempt to acquire the lock
     * @param callable|null $callback
     * @return mixed
     */
    public function get(mixed $callback = null): mixed;

    /**
     * Attempt to acquire the lock for the given number of seconds
     * @param $seconds
     * @param callable|null $callback
     * @return mixed
     */
    public function block($seconds, mixed $callback = null): mixed;

    /**
     * Release the lock
     * @return mixed
     */
    public function release(): bool;

    /**
     * Returns the current owner of the lock
     * @return mixed
     */
    public function owner(): mixed;

    /**
     * Releases this lock in disregard of ownership.
     * @return mixed
     */
    public function forceRelease(): mixed;
}
