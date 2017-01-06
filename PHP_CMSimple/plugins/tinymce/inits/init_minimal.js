var tinyConfig = {
    selector: "cmsimple-editor",
    skin: "cmsimple",
    block_formats: "h1 page=h1;h2 page=h2;h3 page=h3;Div=div;Paragraph=p;Header 4=h4;Header 5=h5;Header 6=h6;code=code;pre=pre",
    toolbar_items_size: "small",
    fontsize_formats: "8px 10px 12px 14px 15px 16px 18px 20px 24px 26px 30px 36px",
    entity_encoding : "named",
    entities : "160,nbsp",
    element_format : "html",
    extended_valid_elements: "script[type|language|src]",
    image_title: true,
    autosave_ask_before_unload: true,
    plugins: [
        "autosave save advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "media table contextmenu paste importcss emoticons"
    ],
    image_dimensions: false,
    importcss_append: true,
    importcss_groups: [
        {title: "Table styles", filter: /^(td|tr|table)\./},
        {title: "Block styles", filter: /^(div|p|ul|ol|h1|h2|h3|h4|h5|h6)\./},
        {title: "Image styles", filter: /^(img)\./},
        {title: "Other styles"} // The rest
    ],
    menubar: false,
    toolbar: "save code fullscreen searchreplace | formatselect | bold italic | bullist numlist | link unlink | image emoticons | undo redo"
};