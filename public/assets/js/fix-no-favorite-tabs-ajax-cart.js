(function(){
  function removeBadToasts(){
    document.querySelectorAll('.favorite-inline-message,.favorite-toast,.love-toast,.saved-toast,.wishlist-toast,[role="tooltip"]').forEach(function(el){
      var text = (el.textContent || '').toLowerCase();
      if (text.includes('yêu thích') || text.includes('đã lưu') || text.includes('bỏ sản phẩm')) el.remove();
    });
  }
  removeBadToasts();
  new MutationObserver(removeBadToasts).observe(document.documentElement, {childList:true, subtree:true});
})();
