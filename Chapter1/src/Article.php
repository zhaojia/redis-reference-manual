<?php
/**
 * Created by PhpStorm.
 * User: WytheHuang
 * Date: 2019/12/17
 * Time: 23:00
 */
declare(strict_types = 1);
namespace Wythe\Redis\Chapter1\src;


use Wythe\Redis\Client;

/**
 * Class Article
 * @package Wythe\Redis\Chapter1\src
 */
class Article
{
    /**
     * 文章ID
     * @var int
     */
    private $id;

    /**
     * 标题缓存KEY
     * @var string
     */
    private $titleKey;

    /**
     * 内容缓存KEY
     * @var string
     */
    private $contentKey;

    /**
     * 作者缓存KEY
     * @var string
     */
    private $authorKey;

    /**
     * 创建时间
     * @var string
     */
    private $createAtKey;

    /**
     * redis实例
     * @var \Wythe\Redis\Client
     */
    private $client;

    /**
     * Article constructor.
     * @param \Wythe\Redis\Client $client
     * @param int    $id
     */
    public function __construct(Client $client, int $id)
    {
        $this->client = $client;
        $this->id = $id;
        $this->titleKey = "article::{$id}::title";
        $this->contentKey = "article::{$id}::content";
        $this->authorKey = "article::{$id}::author";
        $this->createAtKey = "article::{$id}::create_at";
    }

    /**
     * 缓存文章
     * @param string $title   标题
     * @param string $content 内容
     * @param string $author  作者
     * @return bool
     */
    public function create(string $title, string $content, string $author): bool
    {
        $data = [
            $this->titleKey => $title,
            $this->contentKey => $content,
            $this->authorKey => $author,
            $this->createAtKey => date('Y-m-d'),
        ];
        return $this->client->handler()->msetnx($data) === 1;
    }

    /**
     * 获取文章缓存
     * @return array
     */
    public function get(): array
    {
        $result = $this->client->handler()->mget([$this->titleKey, $this->contentKey, $this->authorKey, $this->createAtKey]);
        return ['id' => $this->id, 'title' => $result[0], 'content' => $result[1], 'author' => $result[2], 'create_at' => $result[3]];
    }

    /**
     * 更新文章缓存
     * @param string $title   标题
     * @param string $content 内容
     * @param string $author  作者
     * @return bool
     */
    public function update(string $title = '', string $content = '', string $author = ''): bool
    {
        $data = [];
        if ($title !== '') {
            $data[$this->titleKey] = $title;
        }
        if ($content !== '') {
            $data[$this->contentKey] = $content;
        }
        if ($author !== '') {
            $data[$this->authorKey] = $author;
        }
        if ($data) {
            return $this->client->handler()->mset($data);
        }
        return false;
    }

    /**
     * 获取文章长度
     * @return int
     */
    public function getContentLen(): int
    {
        return $this->client->handler()->strlen($this->contentKey);
    }

    /**
     * 预览文章内容
     * @param int $previewLen 预览字数
     * @return string
     */
    public function getContentPreview(int $previewLen): string
    {
        $startIndex = 0;
        $endIndex = $previewLen - 1;
        return $this->client->handler()->getRange($this->contentKey, $startIndex, $endIndex);
    }
}