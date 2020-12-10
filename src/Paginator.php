<?php

namespace Kansept\Dbi;

class Paginator
{
    const LIMIT = 50;

    private $conn;
    private $limit;
    private $page;
    private $query;
    private $param;
    private $total;

    /**
     * Конструктор класса
     *
     * @param Db $conn
     * @param string $query
     * @param array $param
     */
    public function __construct($db, $query, array $param = [])
    {
        $this->conn = $db;
        $this->query = $query;
        $this->param = $param;
    }

    /**
     * Получить данные
     *
     * @param integer $limit
     * @param integer $page
     * @return \stdClass
     */
    public function getData($limit = 10, $page = 1)
    {
        $this->limit = ($limit === null) ? self::LIMIT : $limit;
        $this->page = ($page === null) ? 1 : $page;

        if ($this->limit == 'all') {
            $query = $this->query;
        } else {
            $query = $this->query . " LIMIT " . (((int)$this->page - 1) * (int)$this->limit) . ", " . (int)$this->limit;
        }

        $query = preg_replace('/SELECT/', 'SELECT SQL_CALC_FOUND_ROWS ', $query, 1);

        $results = $this->conn->exec($query, $this->param)->rows;
        $total = $this->conn->exec('SELECT FOUND_ROWS() `rows`')->row;
        $this->total = $total['rows'];

        $result        = new \stdClass();
        $result->page  = $this->page;
        $result->limit = $this->limit;
        $result->total = $this->total;
        $result->data  = $results;

        return $result;
    }

    /**
     * Создание ссылок
     *
     * @param int $links
     * @param string $list_class
     * @return void
     */
    public function createLinks($links, $list_class)
    {
        if ($this->limit == 'all') {
            return '';
        }

        $last = ($this->limit != 0) ? ceil($this->total / $this->limit) : 0;

        $start = (($this->page - $links) > 0) ? $this->page - $links : 1;
        $end = (($this->page + $links) < $last) ? $this->page + $links : $last;

        $html = '<ul class="' . $list_class . '">';

        $class = ($this->page == 1) ? "disabled" : "";
        $html .= '<li class="' . $class . '"><a href="' . $this->setGetParam(['limit' => $this->limit, 'page' => ($this->page - 1)]) . '">&laquo;</a></li>';

        if ($start > 1) {
            $html .= '<li><a href="' . $this->setGetParam(['limit' => $this->limit, 'page' => 1]) . '">1</a></li>';
            $html .= '<li class="disabled"><span>...</span></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $class = ($this->page == $i) ? "active" : "";
            $html .= '<li class="' . $class . '"><a href="' . $this->setGetParam(['limit' => $this->limit, 'page' => $i]) . '">' . $i . '</a></li>';
        }

        if ($end < $last) {
            $html .= '<li class="disabled"><span>...</span></li>';
            $html .= '<li><a href="' . $this->setGetParam(['limit' => $this->limit, 'page' => $last]) . '">' . $last . '</a></li>';
        }

        $class = ($this->page == $last) ? "disabled" : "";
        $html .= '<li class="' . $class . '"><a href="' . $this->setGetParam(['limit' => $this->limit, 'page' => ($this->page + 1)]) . '">&raquo;</a></li>';

        $html .= '</ul>';

        return $html;
    }

    public function createLimits($current, $ranges = null)
    {
        if ($ranges === null) {
            $ranges = [50, 100, 300, 500, 1000];
        }

        $getParam = [];
        $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        parse_str($query, $getParam);
        if (isset($getParam['limit'])) {
            unset($getParam['limit']);
        }

        $html = '
            <div class="pagination">
                <form action="' . $_SERVER['REQUEST_URI'] . '" method="get" id="paginateLimit" class="form-inline">
                    <span>Показать на странице</span>
                    <select name="limit" class="form-control" onchange="document.getElementById(\'paginateLimit\').submit();">';

        foreach ($ranges as $range) {
            $selected = ($current == $range) ? 'selected' : '';
            $html .= "<option value=\"{$range}\" {$selected}>{$range}</option>";
        }

        $html .= '
                    </select>';
        foreach ($getParam as $name => $value) {
            $html .= "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
        }

        $html .= '
                </form>
            </div>';

        return $html;
    }

    /**
     * Замена GET параметров в ссылке
     *
     * @param array $param 
     * @param string $url [option] 
     * @return void
     */
    private static function setGetParam(array $param, $url = null)
    {
        if ($url === null) {
            $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $parse_url = parse_url($url);
        $get_params = [];

        if (isset($parse_url['query'])) {
            parse_str($parse_url['query'], $get_params);
        }
        foreach ($param as $key => $value) {
            $get_params[$key] = $value;
        }

        $link = http_build_query($get_params);

        return $_SERVER['REQUEST_SCHEME'] . '://' . $parse_url['path'] . '?' . $link;
    }
}
