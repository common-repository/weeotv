<?php

/**
 * @requires WordLiftPlugin [12]\.
 */
class WeeoTvService {
    
    /**
     * @service ajax
     * @action weeotv.categories
     * @authentication none
     */
     public function getCategoriesAsHierarchy() {
         $categoryService = new CategoryService();
         return $categoryService->getCategoriesAsHierarchy();
     }
     
     /**
      * @service ajax
      * @action weeotv.posts_by_category_slug
      * @authentication none
      */
     public function findPostsByCategorySlug($slug, $subcats = false, $offset = 0, $limit = -1) {

         $postService = new PostService();
         $types = array(
         			WordLiftPlugin::POST_TYPE,
         			'post'
         		);

         $subcats = ('true' === $subcats);
         $query = $postService->findByCategorySlug($slug, $types, $subcats, $offset, $limit);
         $posts = &$query['posts'];
         $posts = $postService->loadCustomFields($posts);
         $posts = $postService->loadCategories($posts);
         $posts = $postService->loadAuthors($posts);
         $posts = $postService->loadTags($posts);

         return $query;
     }
     
     /**
      * @service ajax
      * @action weeotv.posts_by_slugs
      * @authentication none
      */
     public function findPostsBySlugs($slugs) {
         $postService = new PostService();
         $types = array(
         			WordLiftPlugin::POST_TYPE,
         			'post'
         		);

         $slugs = explode(',', $slugs);
         $posts = $postService->findBySlugNames($slugs);
         $posts = $postService->loadCustomFields($posts);

         return $posts;
     }
     
     /**
      * @service ajax
      * @action weeotv.related_posts
      * @authentication none
      */
     public function findRelatedPosts($id, $offset = 0, $limit = 20) {
         $postService = new PostService();
         $types = array(
                    WordLiftPlugin::POST_TYPE,
         			'post'
         		);

         $posts = $postService->findRelated($id, $types, $offset, $limit);
         $posts = $postService->loadCustomFields($posts);
         $posts = $postService->loadCategories($posts);
         $posts = $postService->loadAuthors($posts);

         return $posts;
     }
     
     /**
      * @service ajax
      * @action weeotv.post_by_id
      * @authentication none
      */
     public function findPostById($id) {
     
        $types = array(
            WordLiftPlugin::POST_TYPE
        );

        $postService = new PostService();

        $posts = $postService->findAll(
            array('p'=>$id),
            $types
        );
        $posts = $postService->loadCustomFields($posts);
        $posts = $postService->loadCategories($posts);
        $posts = $postService->loadAuthors($posts);
        $posts = $postService->loadTags($posts);

        return $posts;
    }
     
     /**
      * @service ajax
      * @action weeotv.geo_rss
      * @authentication none
      */
     public function getGeoRss($categories = null, $years = null) {

         echo <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss">
    <title>WeeoTv</title>

EOD;

         $args = array(
     		'post_type' => array(
                WordLiftPlugin::POST_TYPE
             ),
     		'meta_query' => array(
     			'relation' => 'AND',
     			array(
     				'key' => WordLiftPlugin::FIELD_PREFIX . 'contentlocation',
     				'value' => '',
     				'compare' => '!='
     			)
     		)
         );

         if (null !== $categories && "" !== $categories) {
         	$args = array_merge_recursive(
         				$args,
         				array(
         					'category_name' => $categories
         				)
         			);
         }

         if (null !== $years && "" !== $years) {
         	$args = array_merge_recursive(
         			$args,
         			array(
         				'meta_query' => array(
         					array(
         						'key' => WordLiftPlugin::FIELD_PREFIX . 'datepublished',
         						'value' => explode(',', $years),
         						'compare' => 'IN'
         					)
         				)
         			)
         		);
         }

         $postService = new PostService();
         $posts = $postService->findAll($args, $types);
         $posts = $postService->loadCustomFields($posts);
         $posts = $postService->loadCategories($posts);

         foreach ($posts as $post) {

         	$thumbnailURL = $post['custom_fields'][WordLiftPlugin::FIELD_PREFIX . 'thumbnailurl'][0];
         	$title = $post['post_title'];
         	$id = $post['ID'];
         	$categoryTerm = $post['categories'][0]['slug'];

         	$placeSlugNames = $post['custom_fields'][WordLiftPlugin::FIELD_PREFIX . 'contentlocation'];
         	$places = $postService->findBySlugNames($placeSlugNames);
         	$places = $postService->loadCustomFields($places);

         	foreach ($places as $place) {

         		$geoCoordinatesSlugNames = $place['custom_fields'][WordLiftPlugin::FIELD_PREFIX . 'geo']; 
         		$geoCoordinates = $postService->findBySlugNames($geoCoordinatesSlugNames);
         		$geoCoordinates = $postService->loadCustomFields($geoCoordinates);

         		foreach ($geoCoordinates as $geoCoordinate) {

         			$latitude = $geoCoordinate['custom_fields'][WordLiftPlugin::FIELD_PREFIX . "latitude"][0];
         			$longitude = $geoCoordinate['custom_fields'][WordLiftPlugin::FIELD_PREFIX . "longitude"][0];

                    echo <<<EOD
 	<entry>
 		<title>$title</title>
 		<id>$id</id>
 		<georss:point>$latitude $longitude</georss:point>
 		<thumbnail url="$thumbnailURL" />
 		<category term="$categoryTerm" />
 	</entry>

EOD;
         		}
         	}
         }

echo "</feed>\n";

        return AjaxService::CALLBACK_RETURN_NULL;
     } 
     
}

?>