/**
 * Quterma Theme - Vanilla JavaScript Bundle
 *
 * @package Quterma
 */

(function () {
  'use strict';

  // 1. HEADER SCROLL & SCROLL TOP & MOBILE AUTO-HIDE ON SCROLL
  var hdr = document.getElementById('header');
  var scrollTopBtn = document.getElementById('scrollTopBtn');
  var lastScrollY = window.scrollY || 0;
  var mm = document.getElementById('mobMenu');
  var mo = document.getElementById('mobOverlay');
  var so = document.getElementById('srchOverlay');

  window.addEventListener('scroll', function () {
    var currentY = window.scrollY || 0;
    if (hdr) {
      hdr.classList.toggle('scrolled', currentY > 8);

      // Mobile header hide on scroll down, show on scroll up
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
    }
    if (scrollTopBtn) {
      scrollTopBtn.classList.toggle('vis', currentY > 400);
    }
    lastScrollY = currentY;
  }, { passive: true });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      if (hdr) hdr.classList.remove('hdr-hidden');
    });
  }

  // 2. MOBILE MENU DRAWER
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
  // 7. GASTROGUIDE FILTERING (Города, Кухня, Цена, Особенности)
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
    var curFeature = 'all';

    function applyVenues() {
      var any = false;
      cards = document.querySelectorAll('.venue-card');
      cards.forEach(function (card) {
        var cardCity     = card.getAttribute('data-city');
        var cardType     = (card.getAttribute('data-type') || '').toLowerCase();
        var cardPrice    = card.getAttribute('data-price');
        var cardFeatures = (card.getAttribute('data-features') || '').toLowerCase();

        var matchCity    = (curCity === 'all' || cardCity === curCity);
        var matchType    = (curType === 'all' || cardType.indexOf(curType) !== -1);
        var matchPrice   = (curPrice === 'all' || cardPrice === curPrice);
        var matchFeature = (curFeature === 'all' || cardFeatures.indexOf(curFeature) !== -1);

        var show = matchCity && matchType && matchPrice && matchFeature;
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
        cityFilts.forEach(function (x) {
          var isActive = (x === b);
          x.classList.toggle('active', isActive);
          x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        applyVenues();
      });
    });

    if (venueReset) {
      venueReset.addEventListener('click', function () {
        curCity    = 'all';
        curType    = 'all';
        curPrice   = 'all';
        curFeature = 'all';
        cityFilts.forEach(function (x) {
          var isAll = (x.getAttribute('data-city') === 'all');
          x.classList.toggle('active', isAll);
          x.setAttribute('aria-pressed', isAll ? 'true' : 'false');
        });
        if (extra) {
          extra.querySelectorAll('.tag').forEach(function (t) {
            t.classList.remove('active');
            t.setAttribute('aria-pressed', 'false');
          });
        }
        applyVenues();
      });
    }

    if (moreBtn && extra) {
      moreBtn.addEventListener('click', function () {
        var isOpen = extra.classList.toggle('open');
        moreBtn.classList.toggle('open', isOpen);
        moreBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });

      extra.querySelectorAll('.tag[data-filter-type]').forEach(function (t) {
        t.addEventListener('click', function () {
          var val = (t.getAttribute('data-filter-type') || '').toLowerCase();
          if (curType === val) {
            curType = 'all';
            t.classList.remove('active');
            t.setAttribute('aria-pressed', 'false');
          } else {
            extra.querySelectorAll('.tag[data-filter-type]').forEach(function (x) {
              x.classList.remove('active');
              x.setAttribute('aria-pressed', 'false');
            });
            curType = val;
            t.classList.add('active');
            t.setAttribute('aria-pressed', 'true');
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
            t.setAttribute('aria-pressed', 'false');
          } else {
            extra.querySelectorAll('.tag[data-filter-price]').forEach(function (x) {
              x.classList.remove('active');
              x.setAttribute('aria-pressed', 'false');
            });
            curPrice = val;
            t.classList.add('active');
            t.setAttribute('aria-pressed', 'true');
          }
          applyVenues();
        });
      });

      extra.querySelectorAll('.tag[data-filter-feature]').forEach(function (t) {
        t.addEventListener('click', function () {
          var val = (t.getAttribute('data-filter-feature') || '').toLowerCase();
          if (curFeature === val) {
            curFeature = 'all';
            t.classList.remove('active');
            t.setAttribute('aria-pressed', 'false');
          } else {
            extra.querySelectorAll('.tag[data-filter-feature]').forEach(function (x) {
              x.classList.remove('active');
              x.setAttribute('aria-pressed', 'false');
            });
            curFeature = val;
            t.classList.add('active');
            t.setAttribute('aria-pressed', 'true');
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
        var cCity = c.getAttribute('data-city') || '';
        var cWhen = c.getAttribute('data-when') || '';
        var whenList = cWhen.split(/\s+/);

        var matchCity = (curCity === 'all' || cCity === curCity);
        var matchWhen = (curWhen === 'all' || whenList.indexOf(curWhen) !== -1);
        var show = matchCity && matchWhen;

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
        cityFilts.forEach(function (x) {
          var isActive = (x === b);
          x.classList.toggle('active', isActive);
          x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        applyEvents();
      });
    });

    dateFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        curWhen = b.getAttribute('data-when');
        dateFilts.forEach(function (x) {
          var isActive = (x === b);
          x.classList.toggle('active', isActive);
          x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        applyEvents();
      });
    });

    if (eventReset) {
      eventReset.addEventListener('click', function () {
        curCity = 'all';
        curWhen = 'all';
        cityFilts.forEach(function (x) {
          var isAll = (x.getAttribute('data-city') === 'all');
          x.classList.toggle('active', isAll);
          x.setAttribute('aria-pressed', isAll ? 'true' : 'false');
        });
        dateFilts.forEach(function (x) {
          var isAll = (x.getAttribute('data-when') === 'all');
          x.classList.toggle('active', isAll);
          x.setAttribute('aria-pressed', isAll ? 'true' : 'false');
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
