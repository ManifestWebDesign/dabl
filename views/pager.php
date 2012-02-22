<?php

/*
 * @param string $url_format The url to format paging links with, ie: /actu/index.html?page=$page_num. $page_num to be replaced with page number.
 * @param int $page_limit For limiting the number of page number links
 */

if (!isset($url_format)) {
	$args = (array) @$_GET;
	unset($args['_url']);
	$args['page'] = 'page_num';
	$url_format = @$_GET['_url'] . '?' . http_build_query($args);
}

if (!isset($page_limit)) {
	$page_limit = 9;
}

$page_limit = intval($page_limit);

$mid_page_limit = $page_limit >> 1;
$page = $pager->getPageNum();
$count = $pager->getPageCount();
$start = max(1, min($count - $page_limit, $page - $mid_page_limit));
$end = min($count, max($page_limit, $page + $mid_page_limit));

if ($count < 2){
	return;
}
?>
<div class="pager ui-helper-clearfix">
<span class="pager-label">
Page
</span>
<?php
if ($page > 1):
	$link = site_url(str_replace('page_num', 1, $url_format));
?>
	<a href="<?php echo $link ?>">&laquo; First</a>

<?php $link = site_url(str_replace('page_num', $page - 1, $url_format)) ?>

	<a href="<?php echo $link ?>">&lsaquo; Previous</a>

<?php endif; ?>

<?php for ($i = $start; $i <= $end; ++$i): ?>
	<?php if ($i == $page): ?>

	<span><?php echo $i ?></span>

	<?php else: ?>
		<?php $link = site_url(str_replace('page_num', $i, $url_format)); ?>

	<a href="<?php echo $link ?>"><?php echo $i ?></a>

	<?php endif ?>
<?php endfor; ?>

<?php if ($page < $count): ?>
	<?php $link = site_url(str_replace('page_num', $page + 1, $url_format)); ?>

	<a href="<?php echo $link ?>">Next &rsaquo;</a>

	<?php $link = site_url(str_replace('page_num', $count, $url_format)); ?>
	<a href="<?php echo $link ?>">Last &raquo;</a>

<?php endif ?>
</div>