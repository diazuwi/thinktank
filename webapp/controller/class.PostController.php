<?php
/**
 * Post Controller
 *
 * Displays a post and its replies, retweets, and republishable replies in tabs
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class PostController extends ThinkTankAuthController {
    /**
     *
     * @var PostDAO
     */
    var $post_dao;
    /**
     * Constructor
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        global $db; //@TODO remove this when PDO port is done
        $this->post_dao = new PostDAO($db);
        $this->addToView('controller_title', 'Post details');
    }

    /**
     * Main control method
     */
    public function auth_control() {
        $this->setViewTemplate('post.index.tpl');
        if ( isset($_REQUEST['t']) && is_numeric($_REQUEST['t']) && $this->post_dao->isPostInDB($_REQUEST['t']) ){
            $post_id = $_REQUEST['t'];
            $this->addToViewCacheKey($post_id);
            $post = $this->post_dao->getPost($post_id);
            $this->addToView('post', $post);

            $this->addToView('likely_orphans', $this->post_dao->getLikelyOrphansForParent($post->pub_date, $post->author_user_id,$post->author_username, 15) );
            $this->addToView('all_tweets', $this->post_dao->getAllPosts($post->author_user_id, 15) );

            $all_replies = $this->post_dao->getRepliesToPost($post_id);
            $this->addToView('replies', $all_replies );

            $all_replies_count = count($all_replies);
            $this->addToView('reply_count', $all_replies_count );

            $all_retweets = $this->post_dao->getRetweetsOfPost($post_id);
            $this->addToView('retweets', $all_retweets );

            $retweet_reach = $this->post_dao->getPostReachViaRetweets($post_id);
            $this->addToView('retweet_reach', $retweet_reach);

            $public_replies = $this->post_dao->getPublicRepliesToPost($post_id);
            $public_replies_count = count($public_replies);
            $this->addToView('public_reply_count', $public_replies_count );

            $private_replies_count = $all_replies_count - $public_replies_count;
            $this->addToView('private_reply_count', $private_replies_count );
        } else {
            $this->addToView('error', 'Post not found');
        }
        return $this->generateView();
    }
}