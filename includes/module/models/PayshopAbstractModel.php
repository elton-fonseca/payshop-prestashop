<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
abstract class PayshopAbstractModel
{
    protected $table;
    protected $where;
    protected $columns;
    protected $orderBy;
    protected $andWhere;

    public function __construct()
    {
        $this->columns = '*';
    }

    /**
     * Execute query for database without return
     *
     * @param string $query
     *
     * @return bool
     */
    public function executeQuery($query)
    {
        if (Db::getInstance()->execute($query) == false) {
            PayshopLog::generate('PayshopAbstractModel: Failed to execute query: ' . Db::getInstance()->getMsgError(), 'error');

            return false;
        }

        return true;
    }

    /**
     * Execute query for database returning one row
     *
     * @param string $query
     *
     * @return array|bool|object|null
     */
    public function selectQuery($query)
    {
        $sql = Db::getInstance()->getRow($query);

        return $sql;
    }

    /**
     * Execute query for database returning many rows
     *
     * @param string $query
     *
     * @return array|bool|object|null
     */
    public function selectMany($query)
    {
        $sql = Db::getInstance()->executeS($query);

        return $sql;
    }

    /**
     * Get method
     */
    public function get()
    {
        $query = "SELECT $this->columns FROM $this->table $this->where $this->orderBy";
        $result = $this->selectQuery($query);

        return $result;
    }

    /**
     * Get all method
     */
    public function getAll()
    {
        $query = "SELECT $this->columns FROM $this->table $this->where $this->orderBy";
        $result = $this->selectMany($query);

        return $result;
    }

    /**
     * Count method, needs where() method
     *
     * @return mixed
     */
    public function count()
    {
        $query = "SELECT COUNT(*) AS count FROM $this->table $this->where $this->andWhere";
        $result = $this->selectQuery($query);

        return $result['count'];
    }

    /**
     * Set columns method, needs be called with select()
     *
     * @param array $columns
     *
     * @return AbstractModel
     */
    public function columns($columns)
    {
        if (gettype($columns) == 'array') {
            $this->columns = implode(',', $columns);
        }

        return $this;
    }

    /**
     * Where method, needs be called with count() or get()
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return AbstractModel
     */
    public function where($column, $operator, $value)
    {
        $this->where = 'WHERE ' . $column . ' ' . $operator . ' "' . $value . '"';

        return $this;
    }

    /**
     * And where method, needs be called with count() or get()
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return AbstractModel
     */
    public function andWhere($column, $operator, $value)
    {
        $this->andWhere = 'AND ' . $column . ' ' . $operator . ' "' . $value . '"';

        return $this;
    }

    /**
     * orderBy method, needs be called with get()
     *
     * @param string $column
     * @param string $operator
     *
     * @return AbstractModel
     */
    public function orderBy($column, $operator)
    {
        $this->orderBy = 'ORDER BY ' . $column . ' ' . $operator;

        return $this;
    }

    /**
     * Insert data in database
     *
     * @param array $array
     *
     * @return bool|void
     */
    public function create($array)
    {
        if (gettype($array) == 'array') {
            $attrs = '';
            $params = '';

            foreach ($array as $attr => $param) {
                $attrs .= $attr . ',';
                $params .= "'" . $param . "',";
            }

            $attrs .= 'created_at';
            $params .= "'" . date('Y-m-d H:i:s') . "'";

            $query = "INSERT INTO $this->table ($attrs) VALUES ($params)";
            $result = $this->executeQuery($query);

            return $result;
        }

        return false;
    }

    /**
     * Update data in database
     *
     * @param array $array
     *
     * @return bool|void
     */
    public function update($array)
    {
        if (gettype($array) == 'array') {
            $update = '';

            foreach ($array as $attr => $param) {
                $update .= $attr . " = '" . $param . "',";
            }

            $update .= "updated_at = '" . date('Y-m-d H:i:s') . "'";
            $query = "UPDATE $this->table SET $update $this->where";
            $result = $this->executeQuery($query);

            return $result;
        }

        return false;
    }

    /**
     * Delete data from database
     *
     * @return bool|void
     */
    public function destroy()
    {
        $query = "DELETE FROM $this->table $this->where";
        $result = $this->executeQuery($query);

        return $result;
    }
}
