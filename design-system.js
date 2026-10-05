/* ================================================================
   КУТЕРЬМА — ОБЩАЯ ЛОГИКА ШАПКИ/ПОДВАЛА/ПОИСКА
   Подключается на каждой странице. Специфичная для конкретной
   страницы логика (фильтры своей ленты, карусель и т.п.) остаётся
   в собственном <script> каждой страницы.
   ================================================================ */
(function(){
'use strict';

// HEADER SCROLL SHADOW + SCROLL-TOP VISIBILITY + MOBILE AUTO-HIDE ON SCROLL
var hdr=document.getElementById('header');
var lastScrollY = window.scrollY || 0;
var mm=document.getElementById('mobMenu'),mo=document.getElementById('mobOverlay');
var so=document.getElementById('srchOverlay');

if(hdr){
  window.addEventListener('scroll',function(){
    var currentY = window.scrollY || 0;
    hdr.classList.toggle('scrolled', currentY > 8);
    var st=document.getElementById('scrollTopBtn');
    if(st)st.classList.toggle('vis', currentY > 400);

    // Mobile hide header on scroll down, show on scroll up
    if (window.innerWidth <= 768) {
      var isMenuOpen = (mm && mm.classList.contains('open')) || (so && so.classList.contains('open'));
      if (!isMenuOpen) {
        if (currentY > lastScrollY && currentY > 60) {
          hdr.classList.add('hdr-hidden');
        } else if (currentY < lastScrollY || currentY <= 10) {
          hdr.classList.remove('hdr-hidden');
        }
      } else {
        hdr.classList.remove('hdr-hidden');
      }
    } else {
      hdr.classList.remove('hdr-hidden');
    }
    lastScrollY = currentY;
  },{passive:true});
}
var scrollTopBtn=document.getElementById('scrollTopBtn');
if(scrollTopBtn){
  scrollTopBtn.addEventListener('click',function(){
    window.scrollTo({top:0,behavior:'smooth'});
    if(hdr) hdr.classList.remove('hdr-hidden');
  });
}

// MOBILE MENU & FOCUS MANAGEMENT
var previouslyFocusedEl = null;
function getFocusableEls(container) {
  if (!container) return [];
  return Array.prototype.slice.call(
    container.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')
  );
}

function openM(){
  if(mm){
    previouslyFocusedEl = document.activeElement;
    mm.removeAttribute('hidden');
    mm.classList.add('open');
    mm.setAttribute('aria-hidden', 'false');
    if(mo) mo.classList.add('open');
    if(burgerBtn) burgerBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    setTimeout(function(){
      if(mobClose) mobClose.focus();
      else {
        var focusables = getFocusableEls(mm);
        if(focusables.length) focusables[0].focus();
      }
    }, 50);
  }
}

function closeM(){
  if(mm){
    mm.classList.remove('open');
    mm.setAttribute('aria-hidden', 'true');
    if(mo) mo.classList.remove('open');
    if(burgerBtn) burgerBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    if(previouslyFocusedEl && typeof previouslyFocusedEl.focus === 'function'){
      previouslyFocusedEl.focus();
    } else if(burgerBtn){
      burgerBtn.focus();
    }
  }
}

var burgerBtn=document.getElementById('burgerBtn');
if(burgerBtn)burgerBtn.addEventListener('click',function(){
  if(mm && mm.classList.contains('open')) closeM();
  else openM();
});
var mobClose=document.getElementById('mobClose');
if(mobClose)mobClose.addEventListener('click',closeM);
if(mo)mo.addEventListener('click',closeM);

if(mm){
  mm.addEventListener('keydown', function(e){
    if(e.key !== 'Tab') return;
    var focusables = getFocusableEls(mm);
    if(!focusables.length) return;
    var firstEl = focusables[0];
    var lastEl = focusables[focusables.length - 1];
    if(e.shiftKey){
      if(document.activeElement === firstEl){
        e.preventDefault();
        lastEl.focus();
      }
    } else {
      if(document.activeElement === lastEl){
        e.preventDefault();
        firstEl.focus();
      }
    }
  });
}

// SEARCH OVERLAY
var so=document.getElementById('srchOverlay');
var searchOpenBtn=document.getElementById('searchOpenBtn');
if(searchOpenBtn&&so){
  searchOpenBtn.addEventListener('click',function(){
    so.classList.add('open');
    var si=document.getElementById('srchInput');
    if(si)si.focus();
    document.body.style.overflow='hidden';
  });
}
function closeS(){if(so){so.classList.remove('open');document.body.style.overflow='';}}
var srchClose=document.getElementById('srchClose');
if(srchClose)srchClose.addEventListener('click',closeS);
if(so)so.addEventListener('click',function(e){if(e.target===so)closeS();});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){closeS();closeM();}});


// FILTER SLIDERS (desktop buttons + touch scroll + drag to scroll + mouse wheel)
function initFilterSliders(){
  // First, auto-wrap any standalone .filters that are not already inside .filters-slider-wrap
  document.querySelectorAll('.filters').forEach(function(track){
    if(!track.parentElement.classList.contains('filters-slider-wrap')){
      var wrap = document.createElement('div');
      wrap.className = 'filters-slider-wrap';
      track.parentNode.insertBefore(wrap, track);
      
      var prev = document.createElement('button');
      prev.type = 'button';
      prev.className = 'filter-scroll-btn filter-scroll-prev';
      prev.setAttribute('aria-label', 'Назад');
      prev.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>';
      
      var next = document.createElement('button');
      next.type = 'button';
      next.className = 'filter-scroll-btn filter-scroll-next';
      next.setAttribute('aria-label', 'Вперед');
      next.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';
      
      wrap.appendChild(prev);
      wrap.appendChild(track);
      wrap.appendChild(next);
    }
  });

  var filterWraps = document.querySelectorAll('.filters-slider-wrap');
  filterWraps.forEach(function(wrap){
    if(wrap.dataset.sliderInit) return;
    wrap.dataset.sliderInit = 'true';

    var track = wrap.querySelector('.filters');
    var prev = wrap.querySelector('.filter-scroll-prev');
    var next = wrap.querySelector('.filter-scroll-next');
    if(!track) return;

    function updateBtns(){
      var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
      if(prev) prev.classList.toggle('vis', track.scrollLeft > 4);
      if(next) next.classList.toggle('vis', maxScroll > 4 && track.scrollLeft < maxScroll - 4);
    }

    if(prev){
      prev.addEventListener('click', function(e){
        e.preventDefault();
        track.scrollBy({ left: -240, behavior: 'smooth' });
        setTimeout(updateBtns, 200);
      });
    }
    if(next){
      next.addEventListener('click', function(e){
        e.preventDefault();
        track.scrollBy({ left: 240, behavior: 'smooth' });
        setTimeout(updateBtns, 200);
      });
    }

    track.addEventListener('scroll', updateBtns, { passive: true });
    window.addEventListener('resize', updateBtns, { passive: true });
    [50, 150, 300, 600, 1200].forEach(function(delay){
      setTimeout(updateBtns, delay);
    });
    if(document.fonts && document.fonts.ready){
      document.fonts.ready.then(updateBtns);
    }

    // Mouse drag-to-scroll for desktop with drag-vs-click threshold
    var isDown = false, startX = 0, scrollLeftVal = 0, hasMoved = false;
    track.addEventListener('mousedown', function(e){
      if(e.button !== 0) return;
      isDown = true;
      hasMoved = false;
      startX = e.pageX - track.offsetLeft;
      scrollLeftVal = track.scrollLeft;
    });
    track.addEventListener('mouseleave', function(){ isDown = false; });
    window.addEventListener('mouseup', function(){
      if(isDown){
        isDown = false;
        setTimeout(function(){ hasMoved = false; }, 50);
      }
    });
    track.addEventListener('mousemove', function(e){
      if(!isDown) return;
      var x = e.pageX - track.offsetLeft;
      var walk = (x - startX) * 1.5;
      if(Math.abs(walk) > 4){
        hasMoved = true;
      }
      e.preventDefault();
      track.scrollLeft = scrollLeftVal - walk;
      updateBtns();
    });

    // Suppress filter click if dragged
    track.addEventListener('click', function(e){
      if(hasMoved){
        e.preventDefault();
        e.stopPropagation();
      }
    }, true);

    // Auto-center active button on init
    var activeBtn = track.querySelector('.filter.active');
    if(activeBtn && activeBtn.offsetLeft > track.clientWidth / 2){
      track.scrollLeft = activeBtn.offsetLeft - (track.clientWidth - activeBtn.clientWidth) / 2;
      setTimeout(updateBtns, 100);
    }
  });
}
if(document.readyState === 'loading'){
  document.addEventListener('DOMContentLoaded', initFilterSliders);
} else {
  initFilterSliders();
}

// TOP PERIOD SWITCHER (Читают сейчас: сегодня, вчера, неделя, месяц)
function initTopPeriodSwitcher(){
  document.querySelectorAll('.top-period-nav').forEach(function(nav){
    var btns = nav.querySelectorAll('.top-period-btn');
    var container = nav.closest('aside') || nav.parentElement;
    btns.forEach(function(btn){
      btn.addEventListener('click', function(){
        var period = this.getAttribute('data-period');
        btns.forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        if(container){
          var lists = container.querySelectorAll('.top-list');
          lists.forEach(function(list){
            if(list.getAttribute('data-period') === period){
              list.style.display = 'flex';
            } else if(list.getAttribute('data-period')) {
              list.style.display = 'none';
            }
          });
        }
      });
    });
  });
}
if(document.readyState === 'loading'){
  document.addEventListener('DOMContentLoaded', initTopPeriodSwitcher);
} else {
  initTopPeriodSwitcher();
}

// SCROLL REVEAL
var obs=new IntersectionObserver(function(entries){
  entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('on');obs.unobserve(e.target);}});
},{threshold:.07,rootMargin:'0px 0px -30px 0px'});
document.querySelectorAll('.rev').forEach(function(el){obs.observe(el);});

// NAV ACTIVE (visual only — the actual "current page" state should be server-set via .active in markup)
document.querySelectorAll('.nav-pill').forEach(function(el){
  el.addEventListener('click',function(e){
    if(el.getAttribute('href')==='#')e.preventDefault();
  });
});

// FOOTER MARQUEE — guaranteed-seamless loop at any viewport width (see note: this is why
// it's dynamic instead of a fixed copy count — a fixed count breaks on ultrawide monitors).
(function(){
  var track=document.getElementById('footMarquee');
  if(!track||!track.children.length)return;
  var unitHTML=track.children[0].outerHTML;
  var PX_PER_SEC=105;
  var MIN_COPIES=8;
  function rebuild(){
    track.style.animation='none';
    track.innerHTML=unitHTML;
    var spanWidth=track.children[0].getBoundingClientRect().width;
    if(!spanWidth){track.style.animation='';return;}
    var viewportW=window.innerWidth;
    var needed=Math.ceil((viewportW*2.4)/spanWidth);
    if(needed%2!==0)needed+=1;
    needed=Math.max(needed,MIN_COPIES);
    var html='';
    for(var i=0;i<needed;i++){html+=unitHTML;}
    track.innerHTML=html;
    void track.offsetWidth;
    var totalWidth=track.scrollWidth;
    var duration=(totalWidth/2)/PX_PER_SEC;
    track.style.animation='marquee '+duration.toFixed(1)+'s linear infinite';
  }
  rebuild();
  var t;
  window.addEventListener('resize',function(){clearTimeout(t);t=setTimeout(rebuild,250);});
})();

// SIDEBAR TAXONOMY SORTING (Respect editorial data, never strip numbers from tag names)
function initDynamicRubrics(){
  // Only sort sidebar rubrics cloud when real data-count exists from server
  document.querySelectorAll('.sb-rubrics-block .rubrics-cloud').forEach(function(cloud){
    var items = Array.from(cloud.children);
    if(items.length < 2) return;
    var hasCounts = items.some(function(el){ return el.hasAttribute('data-count'); });
    if(!hasCounts) return;
    items.sort(function(a, b){
      var ca = parseInt(a.getAttribute('data-count'), 10) || 0;
      var cb = parseInt(b.getAttribute('data-count'), 10) || 0;
      return cb - ca;
    });
    items.forEach(function(el){ cloud.appendChild(el); });
  });
}
// IN-PLACE LOAD MORE FOR PROTOTYPES
function initPrototypeLoadMore(){
  var btn = document.getElementById('loadMoreBtn');
  var list = document.getElementById('newsFeedList') || document.getElementById('newsList') || document.querySelector('.news-list');
  if(!btn || !list) return;

  btn.addEventListener('click', function(e){
    e.preventDefault();
    var curPage = parseInt(btn.getAttribute('data-page') || '1', 10);
    var maxPage = parseInt(btn.getAttribute('data-max') || '4', 10);
    var nextPage = curPage + 1;

    var orig = btn.innerHTML;
    btn.innerHTML = '<span>Загрузка…</span>';

    setTimeout(function(){
      btn.innerHTML = orig;
      var samples = list.querySelectorAll('.news-card:not(.featured)');
      if(samples.length > 0){
        for(var i=0; i<Math.min(3, samples.length); i++){
          var clone = samples[i].cloneNode(true);
          clone.classList.add('on');
          list.appendChild(clone);
        }
        btn.setAttribute('data-page', nextPage);
        if(nextPage >= maxPage){
          btn.style.display = 'none';
        }
      } else {
        btn.style.display = 'none';
      }
    }, 280);
  });
}

if(document.readyState === 'loading'){
  document.addEventListener('DOMContentLoaded', function(){
    initDynamicRubrics();
    initPrototypeLoadMore();
  });
} else {
  initDynamicRubrics();
  initPrototypeLoadMore();
}

})();
