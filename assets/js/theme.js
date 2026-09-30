/**
 * Quterma Theme - Vanilla JavaScript Bundle
 *
 * @package Quterma
 */

(function () {
  'use strict';

  // 1. HEADER SCROLL & SCROLL TOP
  var hdr = document.getElementById('header');
  var scrollTopBtn = document.getElementById('scrollTopBtn');

  window.addEventListener('scroll', function () {
    if (hdr) {
      hdr.classList.toggle('scrolled', window.scrollY > 8);
    }
    if (scrollTopBtn) {
      scrollTopBtn.classList.toggle('vis', window.scrollY > 400);
    }
  }, { passive: true });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 2. MOBILE MENU DRAWER
  var mm = document.getElementById('mobMenu');
  var mo = document.getElementById('mobOverlay');
  var burgerBtn = document.getElementById('burgerBtn');
  var mobClose = document.getElementById('mobClose');

  function openM() {
    if (mm) mm.classList.add('open');
    if (mo) mo.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeM() {
    if (mm) mm.classList.remove('open');
    if (mo) mo.classList.remove('open');
    document.body.style.overflow = '';
  }

  if (burgerBtn) burgerBtn.addEventListener('click', openM);
  if (mobClose) mobClose.addEventListener('click', closeM);
  if (mo) mo.addEventListener('click', closeM);

  // 3. SEARCH OVERLAY
  var so = document.getElementById('srchOverlay');
  var si = document.getElementById('srchInput');
  var searchOpenBtn = document.getElementById('searchOpenBtn');
  var srchClose = document.getElementById('srchClose');

  function openS() {
    if (so) {
      so.classList.add('open');
      if (si) si.focus();
      document.body.style.overflow = 'hidden';
    }
  }

  function closeS() {
    if (so) {
      so.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  if (searchOpenBtn) searchOpenBtn.addEventListener('click', openS);
  if (srchClose) srchClose.addEventListener('click', closeS);
  if (so) {
    so.addEventListener('click', function (e) {
      if (e.target === so) closeS();
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeS();
      closeM();
    }
  });

  // 4. SCROLL REVEAL ANIMATIONS
  if ('IntersectionObserver' in window) {
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('on');
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.07, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.rev').forEach(function (el) {
      obs.observe(el);
    });
  } else {
    document.querySelectorAll('.rev').forEach(function (el) {
      el.classList.add('on');
    });
  }

  // 5. HERO CAROUSEL
  (function initHeroCarousel() {
    var track = document.getElementById('hcTrack');
    if (!track) return;

    var slides = track.children;
    var total = slides.length;
    if (total <= 1) return;

    var dotsWrap = document.getElementById('hcDots');
    var idx = 0;
    var timer = null;

    if (dotsWrap) {
      dotsWrap.innerHTML = '';
      for (var i = 0; i < total; i++) {
        var d = document.createElement('button');
        d.type = 'button';
        d.className = 'hc-dot' + (i === 0 ? ' active' : '');
        d.setAttribute('aria-label', 'Слайд ' + (i + 1));
        d.setAttribute('data-i', i);
        dotsWrap.appendChild(d);
      }
    }

    var dots = dotsWrap ? dotsWrap.querySelectorAll('.hc-dot') : [];

    function go(n) {
      idx = (n + total) % total;
      track.style.transform = 'translateX(-' + (idx * 100) + '%)';
      dots.forEach(function (dot, i) {
        dot.classList.toggle('active', i === idx);
      });
    }

    function next() { go(idx + 1); }
    function prev() { go(idx - 1); }
    function start() { timer = setInterval(next, 7000); }
    function stop() { if (timer) clearInterval(timer); }

    var nextBtn = document.getElementById('hcNext');
    var prevBtn = document.getElementById('hcPrev');

    if (nextBtn) {
      nextBtn.addEventListener('click', function () { next(); stop(); start(); });
    }
    if (prevBtn) {
      prevBtn.addEventListener('click', function () { prev(); stop(); start(); });
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        go(parseInt(dot.getAttribute('data-i'), 10));
        stop();
        start();
      });
    });

    var wrapEl = document.getElementById('heroCarousel');
    if (wrapEl) {
      wrapEl.addEventListener('mouseenter', stop);
      wrapEl.addEventListener('mouseleave', start);
      wrapEl.addEventListener('touchstart', stop, { passive: true });
      wrapEl.addEventListener('touchend', start, { passive: true });
    }

    start();
  })();

  // 6. HOMEPAGE FEED FILTERS
  (function initFeedFilters() {
    var filts = document.querySelectorAll('.filters .filter[data-filter]');
    var cards = document.querySelectorAll('.news-card[data-category]');
    var feedEmpty = document.getElementById('feedEmpty');
    var emptyReset = document.getElementById('feedEmptyReset');

    if (!filts.length || !cards.length) return;

    function applyFilter(f) {
      filts.forEach(function (x) {
        x.classList.toggle('active', x.getAttribute('data-filter') === f);
      });
      var anyVisible = false;
      cards.forEach(function (c) {
        var cardCat = c.getAttribute('data-category');
        var show = (f === 'all' || cardCat === f);
        c.style.display = show ? '' : 'none';
        if (show) anyVisible = true;
      });
      if (feedEmpty) {
        feedEmpty.style.display = anyVisible ? 'none' : 'flex';
      }
    }

    filts.forEach(function (b) {
      b.addEventListener('click', function () {
        applyFilter(b.getAttribute('data-filter'));
      });
    });

    if (emptyReset) {
      emptyReset.addEventListener('click', function () {
        applyFilter('all');
      });
    }
  })();

  // 7. GASTROGUIDE FILTERING
  (function initGastroguideFilters() {
    var cityFilts = document.querySelectorAll('#cityFilters .filter[data-city]');
    var cards = document.querySelectorAll('.venue-card');
    var venueEmpty = document.getElementById('venueEmpty');
    var venueReset = document.getElementById('venueEmptyReset');
    var moreBtn = document.getElementById('moreFiltersBtn');
    var extra = document.getElementById('extraFilters');

    if (!cityFilts.length && !cards.length) return;

    var curCity = 'all';
    var curType = 'all';
    var curPrice = 'all';

    function applyVenues() {
      var any = false;
      cards.forEach(function (card) {
        var cardCity = card.getAttribute('data-city');
        var cardType = card.getAttribute('data-type');
        var cardPrice = card.getAttribute('data-price');

        var matchCity = (curCity === 'all' || cardCity === curCity);
        var matchType = (curType === 'all' || (cardType && cardType.indexOf(curType) !== -1));
        var matchPrice = (curPrice === 'all' || cardPrice === curPrice);

        var show = matchCity && matchType && matchPrice;
        card.style.display = show ? '' : 'none';
        if (show) any = true;
      });

      if (venueEmpty) {
        venueEmpty.classList.toggle('show', !any);
      }
    }

    cityFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        curCity = b.getAttribute('data-city');
        cityFilts.forEach(function (x) { x.classList.toggle('active', x === b); });
        applyVenues();
      });
    });

    if (venueReset) {
      venueReset.addEventListener('click', function () {
        curCity = 'all';
        curType = 'all';
        curPrice = 'all';
        cityFilts.forEach(function (x) {
          x.classList.toggle('active', x.getAttribute('data-city') === 'all');
        });
        document.querySelectorAll('#extraFilters .tag').forEach(function (t) {
          t.classList.remove('active');
        });
        applyVenues();
      });
    }

    if (moreBtn && extra) {
      moreBtn.addEventListener('click', function () {
        var isOpen = extra.classList.toggle('open');
        moreBtn.classList.toggle('open', isOpen);
      });

      extra.querySelectorAll('.tag[data-filter-type]').forEach(function (t) {
        t.addEventListener('click', function () {
          var val = t.getAttribute('data-filter-type');
          if (curType === val) {
            curType = 'all';
            t.classList.remove('active');
          } else {
            extra.querySelectorAll('.tag[data-filter-type]').forEach(function (x) { x.classList.remove('active'); });
            curType = val;
            t.classList.add('active');
          }
          applyVenues();
        });
      });

      extra.querySelectorAll('.tag[data-filter-price]').forEach(function (t) {
        t.addEventListener('click', function () {
          var val = t.getAttribute('data-filter-price');
          if (curPrice === val) {
            curPrice = 'all';
            t.classList.remove('active');
          } else {
            extra.querySelectorAll('.tag[data-filter-price]').forEach(function (x) { x.classList.remove('active'); });
            curPrice = val;
            t.classList.add('active');
          }
          applyVenues();
        });
      });
    }
  })();

  // 8. EVENTS POSTER FILTERING (API Культура.РФ)
  (function initEventsFilters() {
    var cityFilts = document.querySelectorAll('#cityFilters .filter[data-city]');
    var dateFilts = document.querySelectorAll('#dateFilters .filter[data-when]');
    var cards = document.querySelectorAll('.event-card');
    var eventEmpty = document.getElementById('eventEmpty');
    var eventReset = document.getElementById('eventEmptyReset');

    if (!dateFilts.length) return;

    var curCity = 'all';
    var curWhen = 'all';

    function applyEvents() {
      var any = false;
      cards = document.querySelectorAll('.event-card');
      cards.forEach(function (c) {
        var cCity = c.getAttribute('data-city');
        var cWhen = c.getAttribute('data-when');
        var show = (curCity === 'all' || cCity === curCity) && (curWhen === 'all' || cWhen === curWhen);
        c.style.display = show ? '' : 'none';
        if (show) any = true;
      });

      if (eventEmpty) {
        eventEmpty.classList.toggle('show', !any);
      }
    }

    cityFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        curCity = b.getAttribute('data-city');
        cityFilts.forEach(function (x) { x.classList.toggle('active', x === b); });
        applyEvents();
      });
    });

    dateFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        curWhen = b.getAttribute('data-when');
        dateFilts.forEach(function (x) { x.classList.toggle('active', x === b); });
        applyEvents();
      });
    });

    if (eventReset) {
      eventReset.addEventListener('click', function () {
        curCity = 'all';
        curWhen = 'all';
        cityFilts.forEach(function (x) {
          x.classList.toggle('active', x.getAttribute('data-city') === 'all');
        });
        dateFilts.forEach(function (x) {
          x.classList.toggle('active', x.getAttribute('data-when') === 'all');
        });
        applyEvents();
      });
    }
  })();

  // 9. FOOTER MARQUEE SEAMLESS LOOP
  (function initMarquee() {
    var track = document.getElementById('footMarquee');
    if (!track || !track.children.length) return;

    var unitHTML = track.children[0].outerHTML;
    var PX_PER_SEC = 105;
    var MIN_COPIES = 8;

    function rebuild() {
      track.style.animation = 'none';
      track.innerHTML = unitHTML;
      var spanWidth = track.children[0].getBoundingClientRect().width;
      if (!spanWidth) {
        track.style.animation = '';
        return;
      }
      var viewportW = window.innerWidth;
      var needed = Math.ceil((viewportW * 2.4) / spanWidth);
      if (needed % 2 !== 0) needed += 1;
      needed = Math.max(needed, MIN_COPIES);

      var html = '';
      for (var i = 0; i < needed; i++) {
        html += unitHTML;
      }
      track.innerHTML = html;
      void track.offsetWidth; // force reflow

      var totalWidth = track.scrollWidth;
      var duration = (totalWidth / 2) / PX_PER_SEC;
      track.style.animation = 'marquee ' + duration.toFixed(1) + 's linear infinite';
    }

    rebuild();
    var t;
    window.addEventListener('resize', function () {
      clearTimeout(t);
      t = setTimeout(rebuild, 250);
    });
  })();

})();
