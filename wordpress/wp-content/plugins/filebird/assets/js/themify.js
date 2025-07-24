jQuery(document).ready(function () {
  setTimeout(() => {
    const src = jQuery("[id='module/filebird/main.tsx-js']").attr("src");
    jQuery("[id='module/filebird/main.tsx-js']").remove();
    jQuery("<script>")
      .attr({ id: "module/filebird/main.tsx-js", src: src, type: "module" })
      .appendTo("body");
  }, 200);
});
