/* ================================================================
   КУТЕРЬМА — ОБЩАЯ ЛОГИКА ШАПКИ/ПОДВАЛА/ПОИСКА
   Подключается на каждой странице. Специфичная для конкретной
   страницы логика (фильтры своей ленты, карусель и т.п.) остаётся
   в собственном <script> каждой страницы.
   ================================================================ */
(function(){
'use strict';

// HEADER SCROLL SHADOW + SCROLL-TOP VISIBILITY
var hdr=document.getElementById('header');
if(hdr){
  window.addEventListener('scroll',function(){
    hdr.classList.toggle('scrolled',window.scrollY>8);
    var st=document.getElementById('scrollTopBtn');
    if(st)st.classList.toggle('vis',window.scrollY>400);
  },{passive:true});
}
var scrollTopBtn=document.getElementById('scrollTopBtn');
if(scrollTopBtn){
  scrollTopBtn.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
}

// MOBILE MENU
var mm=document.getElementById('mobMenu'),mo=document.getElementById('mobOverlay');
function openM(){if(mm){mm.classList.add('open');mo.classList.add('open');document.body.style.overflow='hidden';}}
function closeM(){if(mm){mm.classList.remove('open');mo.classList.remove('open');document.body.style.overflow='';}}
var burgerBtn=document.getElementById('burgerBtn');
if(burgerBtn)burgerBtn.addEventListener('click',openM);
var mobClose=document.getElementById('mobClose');
if(mobClose)mobClose.addEventListener('click',closeM);
if(mo)mo.addEventListener('click',closeM);

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

})();
