<?php

function user_is_author()
{
    global $current_user;
    get_currentuserinfo();

    return (get_the_author_meta('user_login') == $current_user->user_login);
}

function get_post_view($postID)
{
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == ''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0'); 
    }

    return ($count <= 1) ? ($count . ' View') : ($count . ' Views');
}

function set_post_view($postID) 
{
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == ''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else if(!user_is_author()){
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

function get_post_new_url()
{
    $post_new_url = admin_url('post-new.php');
    return (is_user_logged_in()) ? $post_new_url : wp_login_url($post_new_url);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function freedom_site_header()
{
    $format = '<div class="header"><a class="site-url" title="%1$s" href="%2$s"><h1 class="site-name">%1$s</h1><h4 class="site-description">%3$s</h4></a></div>';
    $header = sprintf($format, esc_attr(get_bloginfo('name')), esc_url(get_bloginfo('siteurl')), esc_attr(get_bloginfo('description')));

    return $header;
}

function freedom_post_date()
{
    $post_time = esc_html(get_the_date() . ' ' . get_the_time());
    $post_day = esc_html(get_the_date('d'));
    $post_month = esc_html(get_the_date('M'));
    $post_year = esc_html(get_the_date('Y'));

    $format = '<div class="post-date"><a title="%s" href="%s"><h1 class="post-day">%s</h1><h4 class="post-month-year">%s %s</h4></a></div>';
    $post_date = sprintf($format, $post_time, esc_url(get_permalink()), $post_day, $post_month, $post_year);

    return $post_date;
}

function freedom_post_title()
{
    $format = '<a href="%s"><h1 class="post-title">%s</h1></a>';
    $post_title = sprintf($format, esc_url(get_permalink()), esc_html(get_the_title()));

    return $post_title;
}

function freedom_separator()
{
    return '&nbsp;&sdot;&nbsp;';
}

function freedom_post_author()
{
    $format = '<span>by<a class="post-author" href="%s">&nbsp%s</a></span>';
    $post_author = sprintf($format, esc_url(get_author_posts_url(get_the_author_meta('ID'))), esc_html(get_the_author()));

    return $post_author;
}

function freedom_post_view()
{
    $format = '<span class="post-view">%s</span>';
    $post_view = sprintf($format, esc_html(get_post_view(get_the_ID())));

    return $post_view;
}

function freedom_post_comment()
{
    $post_comment = '<a class="comment-link" href="' . esc_url(get_comments_link()) . '">';
    $comment_count = get_comments_number();
    if($comment_count <= 1){
        $post_comment .= $comment_count . ' Comment';
    }else{
        $post_comment .= $comment_count . ' Comments';
    }

    $post_comment .= '</a>';

    return $post_comment;
}

function freedom_post_edit()
{
    $post_edit = '';
    if(current_user_can('edit_posts')){
        $post_edit = '<a class="edit-link" href="' . esc_url(get_edit_post_link(get_the_ID())) . '">Edit</a>';
    }

    return $post_edit;
}

function freedom_post_other_info()
{
    $other_info = '<div class="post-other-info">' . freedom_post_title() . '<h4 class="post-misc-info">' . freedom_post_author();
    $other_info .= freedom_separator() . freedom_post_view();
    $other_info .= freedom_separator() . freedom_post_comment();
    $post_edit = freedom_post_edit();
    if(!empty($post_edit)){
        $other_info .= freedom_separator() . $post_edit;
    }
    $other_info .= '</h4></div>';

    return $other_info;
}

function freedom_post_info()
{
    $post_info = '<div class="post-info">' . freedom_post_date() . freedom_post_other_info() . '</div>';

    return $post_info;
}

function freedom_post_excerpt_echo()
{
    echo '<div class="post-excerpt">';
    echo freedom_post_info();
    echo '<div class="excerpt-content">';
    the_excerpt();
    echo '</div></div>';
}

function freedom_post_content_echo()
{
    echo '<div class="post-content">';
    echo freedom_post_info();
    echo '<div class="content-content">';
    the_content();
    echo '</div></div>';
}

function freedom_no_content_echo($tip)
{
    echo '<div class="no-content">';
    freedom_search_echo($tip);
    echo '</div>';
}

function freedom_main_echo($who)
{
    get_header();
    if(have_posts()){
        while(have_posts()){
            the_post();
            switch($who){
            case 'content':
                freedom_post_content_echo();
                set_post_view(get_the_ID());
                comments_template();
                break;
            default:
                freedom_post_excerpt_echo();
                break;
            }            
        }
    } else {
        freedom_no_content_echo('没有找到任何内容');
    }

    get_sidebar();
    get_footer();
}

function freedom_comment_callback($comment, $args, $depth)
{
    $GLOBAL['comment'] = $comment;
    extract($args, EXTR_SKIP);

    $tag = ($args['style'] == 'div') ? 'div' : 'li';
    $add_below = 'comment';

    $id_attr = sprintf('id="comment-%u"', esc_attr(get_comment_ID()));
    $class_attr = empty($args['has_children']) ? '' : 'class="parent"';
    $comment_author = '<span class="comment_author">' . esc_html(get_comment_author()) . '</span>';
    $comment_date = '<span class="comment_date">' . esc_html(get_comment_date('Y-m-d H:i:s')) . '</span>';

    printf('<%s %s id="comment-%u">', $tag, $class_attr, get_comment_ID());
    $info_format = '<div class="comment-info"><span class="comment-author">%s&nbsp;</span><span class="comment-date">%s</span></div>';
    printf($info_format, esc_html(get_comment_author()), esc_html(get_comment_date('Y-m-d H:i:s')));
    printf('<p>%s%s</p>', esc_html(get_comment_text()), current_user_can('edit_comment') ? '<a href="' . esc_url(get_edit_comment_link()) . '">&nbsp;编辑</a>' : ' ');
    echo '<div class="reply">';
    comment_reply_link(array_merge($args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));
    echo '</div>';
}


function freedom_comment_list_echo()
{
    echo '<ul class="comment-list" id="comments">';
    $options = 'reply_text=回复&avatar_size=0&callback=freedom_comment_callback';
    wp_list_comments($options);
    echo '</ul>';
}

function freedom_comment_form_echo()
{
    $comments = array(
        'title_reply' => '评论',
        'title_reply_to' => '回复%s',
        'cancel_reply_link' => '取消',
        'comment_notes_before' => '<p>您的邮箱不会在页面上显示，带<span class="required">*</span>为必填项</p>',
        'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" aria-required="true"></textarea></p>',    
        'comment_notes_after' => '',
        'label_submit' => '提交'
        );
    comment_form($comments);
}

function freedom_search_echo($tip = '')
{
    echo '<div class="search widget">';
    if($tip != ''){
        echo "<h4>$tip</h4>";
    }
    printf('<form method="get" class="search-form" action="%s">', esc_html(site_url('/')));
    echo '<div><input type="text" value="" name="s" /><input type="submit" value="Search" /></div></form></div>';
}

function freedom_recent_post_echo()
{    
    echo '<div class="recent-post widget"><h4>最近文章</h4><ol>';
    wp_get_archives('type=postbypost&limit=8');
    echo '</ol></div>';
}

function freedom_category_echo()
{
    echo '<div class="category widget"><h4>分类</h4><ul>';
    wp_list_categories('title_li=&show_count=1&show_option_none=');
    echo '</ul></div>';
}

function freedom_tag_cloud_echo()
{
    echo '<div class="tag-cloud widget"><h4>标签云</h4>';
    wp_tag_cloud('smallest=13&argest=18&unit=px&number=30&format=flat&link=view&taxonomy=post_tag&echo=true');
    echo '</div>';
}

function freedom_navigation_echo()
{
    echo '<div class="navigation widget"><h4>导航</h4><ul>';
    echo '<li><a href="' . get_bloginfo('siteurl') . '">首页</a></li>';
    echo '<li><a href="' . get_bloginfo('rss2_url') . '">订阅</a></li>';
    echo '<li><a href="' . get_post_new_url() . '">新文章</a></li>';
    echo '<li><a href="' . admin_url() . '">管理</a></li>';
    $current_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    if(is_user_logged_in()){
        echo '<li><a href="' . wp_logout_url($current_url) . '">注销</a></li>';
    }else{
        echo '<li><a href="' . wp_login_url($current_url) . '">登录</a></li>';
    }
    echo '<li><a href="http://wordpress.org/">WordPress</a><li></ul></div>';
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function freedom_excerpt_length($length)
{
	return 350;
}
add_filter('excerpt_length', 'freedom_excerpt_length');

function freedom_excerpt_more()
{
    return '<a class="read-more" href="'. esc_url(get_permalink()) . '">...阅读全文</a>';
}
add_filter('excerpt_more', 'freedom_excerpt_more');

function freedom_change_default_field($fields)
{
    $commenter = wp_get_current_commenter();
    $required = get_option('require_name_email');
    $aria_required = ($required ? 'aria-required="true"' : '');

    $fields['author'] = '<p class="comment-form-author"><label for="author">昵称</label>' . ($required ? '<span class="required">*</span>' : '') . '<input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '"' . $aria_required . ' /></p>';

    $fields['email'] = '<p class="comment-form-email"><label for="email">邮箱</label>' . ($required ? '<span class="required">*</span>' : '') . '<input id="email" name="email" type="text" value="' . esc_attr($commenter['comment_author_email']) . '"' . $aria_required . ' /></p>';

    if(isset($fields['url'])){
        unset($fields['url']);
    }

    return $fields;
}
add_filter('comment_form_default_fields', 'freedom_change_default_field');

function freedom_change_logged_in_field($field)
{
    return "";
}
add_filter('comment_form_logged_in', 'freedom_change_logged_in_field');

?>