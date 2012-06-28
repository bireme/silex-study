<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Persistence\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Doctrine\Common\Persistence\Mapping\MappingException;

/**
 * The DriverChain allows you to add multiple other mapping drivers for
 * certain namespaces
 *
 * @since  2.2
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class MappingDriverChain implements MappingDriver
{
    /**
     * @var array
     */
    private $drivers = array();

    /**
     * Add a nested driver.
     *
     * @param MappingDriver $nestedDriver
     * @param string $namespace
     */
    public function addDriver(MappingDriver $nestedDriver, $namespace)
    {
        $this->drivers[$namespace] = $nestedDriver;
    }

    /**
     * Get the array of nested drivers.
     *
     * @return array $drivers
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Loads the metadata for the specified class into the provided container.
     *
     * @param string $className
     * @param ClassMetadata $metadata
     *
     * @throws MappingException
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        foreach ($this->drivers as $namespace => $driver) {
            if (strpos($className, $namespace) === 0) {
                $driver->loadMetadataForClass($className, $metadata);
                return;
            }
        }

        throw MappingException::classNotFoundInNamespaces($className, array_keys($this->drivers));
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames()
    {
        $classNames = array();
        $driverClasses = array();
        foreach ($this->drivers AS $namespace => $driver) {
            $oid = spl_object_hash($driver);
            if (!isset($driverClasses[$oid])) {
                $driverClasses[$oid] = $driver->getAllClassNames();
            }

            foreach ($driverClasses[$oid] AS $className) {
                if (strpos($className, $namespace) === 0) {
                    $classNames[$className] = true;
                }
            }
        }
        return array_keys($classNames);
    }

    /**
     * Whether the class with the specified name should have its metadata loaded.
     *
     * This is only the case for non-transient classes either mapped as an Entity or MappedSuperclass.
     *
     * @param string $className
     * @return boolean
     */
    public function isTransient($className)
    {
        foreach ($this->drivers AS $namespace => $driver) {
            if (strpos($className, $namespace) === 0) {
                return $driver->isTransient($className);
            }
        }

        // class isTransient, i.e. not an entity or mapped superclass
        return true;
    }
}