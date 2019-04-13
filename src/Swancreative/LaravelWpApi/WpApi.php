<?php namespace Swancreative\LaravelWpApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WpApi
{

    /**
     * Guzzle client
     * @var Client
     */
    protected $client;

    /**
     * WP-WPI endpoint URL
     * @var string
     */
    protected $endpoint;

    /**
     * Auth headers
     * @var string
     */
    protected $auth;

    /**
     * Constructor
     *
     * @param string $endpoint
     * @param Client $client
     * @param string $auth
     */
    public function __construct($endpoint, Client $client, $auth = null)
    {
        $this->endpoint = $endpoint;
        $this->client   = $client;
        $this->auth     = $auth;
    }

    /**
     * Get all posts
     *
     * @param  int $page
     * @return array
     */
    public function posts($page = null)
    {
        $pages = $page ? ['page' => trim($page)] : null;
        return $this->get('posts', [$pages]);
    }
    /**
     * Get all sticky posts
     *
     * @param  int $page
     * @return array
     */
    public function stickies($page = null)
    {
        $pages = $page ? ['page' => trim($page)] : null;
        return $this->get('posts', ['sticky' => true, $pages]);
    }
    
    /**
     * Get all pages
     *
     * @param  int $page
     * @return array
     */
    public function pages($page = null)
    {
        $pages = $page ? ['page' => trim($page)] : null;
        return $this->get('pages', [$pages]);
    }

    /**
     * Get post by id
     *
     * @param  int $id
     * @return array
     */
    public function postId($id)
    {
        return $this->get("posts/$id");
    }

    /**
     * Get post by slug
     *
     * @param  string $slug
     * @return array
     */
    public function post($slug)
    {
        return $this->get('posts', ['slug' => $slug]);
    }

    /**
     * Get page by slug
     *
     * @param  string $slug
     * @return array
     */
    public function page($slug)
    {
        return $this->get('posts', ['type' => 'page', 'filter' => ['name' => $slug]]);
    }

    /**
     * Get all categories
     *
     * @return array
     */
    public function categories()
    {
        return $this->get('categories',['per_page' => 99]);
    }

    /**
     * Get all tags
     *
     * @return array
     */
    public function tags($tag=null)
    {
        if($tag > 0){
          return $this->get('tags/'.$tag, ['per_page' => 99]);
        }
        return $this->get('tags', ['per_page' => 99]);
    }

    /**
     * Get posts from category
     *
     * @param  string $cat
     * @param  int $page
     * @return array
     */
    public function categoryPosts($cat = null, $page = null, $pp = null)
    {
        $category = $cat ? ['categories' => trim($cat)] : null;
        $pages = $page ? ['page' => trim($page)] : null;
        $pps = $pp ? ['per_page' => trim($pp)] : null;
        return $this->get('posts', [$category, $pages, $pps]);
    }

    /**
     * Get posts by author
     *
     * @param  string $name
     * @param  int $page
     * @return array
     */
    public function authorPosts($name, $page = null)
    {
        $pages = $page ? ['page' => trim($page)] : null;
        return $this->get('posts', [$pages, 'filter' => ['author_name' => $name]]);
    }

    /**
     * Get latest post from category
     *
     * @param  string $cat
     * @return array
     */
    public function latestPost($cat = null)
    {
        $category = $cat ? ['categories' => trim($cat)] : null;
        return $this->get('posts', [ $category, 'per_page' => 1,'status' => 'publish']);
    }
    
    /**
     * Get posts tagged with tag
     *
     * @param  string $tags
     * @param  int $page
     * @return array
     */
    public function tagPosts($tags, $page = null)
    {
        return $this->get('posts', ['tags' => $tags]);
    }

    /**
     * Search posts
     *
     * @param  string $query
     * @param  int $page
     * @return array
     */
    public function search($query, $page = null)
    {
        return $this->get('posts', ['page' => $page, 'filter' => ['s' => $query]]);
    }

    /**
     * Get posts by date
     *
     * @param  int $year
     * @param  int $month
     * @param  int $page
     * @return array
     */
    public function archive($year, $month, $page = null)
    {
        return $this->get('posts', ['page' => $page, 'filter' => ['year' => $year, 'monthnum' => $month]]);
    }

    /**
     * Get data from the API
     *
     * @param  string $method
     * @param  array  $query
     * @return array
     */
    public function get($method, array $query = array())
    {

        try {

            $query = ['query' => $query];

            if ($this->auth) {
                $query['auth'] = $this->auth;
            }

            $response = $this->client->get($this->endpoint . $method, $query);

            $return = [
                'results' => json_decode((string) $response->getBody(), true),
                'total'   => $response->getHeaderLine('X-WP-Total'),
                'pages'   => $response->getHeaderLine('X-WP-TotalPages')
            ];

        } catch (RequestException $e) {

            $error['message'] = $e->getMessage();

            if ($e->getResponse()) {
                $error['code'] = $e->getResponse()->getStatusCode();
            }

            $return = [
                'error'   => $error,
                'results' => [],
                'total'   => 0,
                'pages'   => 0
            ];

        }

        return $return;

    }
}

