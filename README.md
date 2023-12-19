There's quite a lot of things I'd do differently but I was hit by a feaver and by the time I was somewhat capable of doing anything I was extremely limited with time so it is what it is.
I'd definitely write a differrent html that would lead me to a way more efficient css (without so many repetitions).
Also, I just hooked it to wp_footer to make it show on all blog posts regardless of the theme. There's no universal "after_content" hook that all themes have so I went for wp_footer.
Of course, way better solution was to put the entire html in variable and append it to $content then use a filter. Something like this:

function additional_content($content) {
        $additional_content = '<div class="something">ENTIRE HTML HERE</div>';
        $content .= $additional_content;
        return $content;
}
add_filter('the_content', 'additional_content');

Even this is not perfect because I worked on multiple websites where content area is completely disabled and the_content(); isn't even included in the post/page template so the application would depend on the theme used in a specific website.
I did have an idea to create an admin page and add a dropdown to choose where to show the voting "widget" but you can refer to line 1 to see the reason why I didn't do that :D

If what you see works for you, I'd be more than happy to discuss it further.
