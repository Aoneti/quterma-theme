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

  // 2. MOBILE MENU DRAWER & ACCESSIBLE FOCUS TRAP
  var burgerBtn = document.getElementById('burgerBtn');
  var mobClose = document.getElementById('mobClose');
  var previouslyFocusedElement = null;

  function getFocusableElements(container) {
    if (!container) return [];
    return Array.prototype.slice.call(
      container.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')
    );
  }

  function openM() {
    if (!mm) return;
    previouslyFocusedElement = document.activeElement;
    mm.removeAttribute('hidden');
    mm.classList.add('open');
    mm.setAttribute('aria-hidden', 'false');
    if (mo) mo.classList.add('open');
    if (burgerBtn) burgerBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';

    setTimeout(function () {
      if (mobClose) {
        mobClose.focus();
      } else {
        var focusables = getFocusableElements(mm);
        if (focusables.length) focusables[0].focus();
      }
    }, 50);
  }

  function closeM() {
    if (!mm) return;
    mm.classList.remove('open');
    mm.setAttribute('aria-hidden', 'true');
    if (mo) mo.classList.remove('open');
    if (burgerBtn) burgerBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';

    if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
      previouslyFocusedElement.focus();
    } else if (burgerBtn) {
      burgerBtn.focus();
    }
  }

  if (burgerBtn) burgerBtn.addEventListener('click', function () {
    if (mm && mm.classList.contains('open')) {
      closeM();
    } else {
      openM();
    }
  });
  if (mobClose) mobClose.addEventListener('click', closeM);
  if (mo) mo.addEventListener('click', closeM);

  if (mm) {
    mm.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      var focusables = getFocusableElements(mm);
      if (!focusables.length) return;
      var firstEl = focusables[0];
      var lastEl = focusables[focusables.length - 1];

      if (e.shiftKey) {
        if (document.activeElement === firstEl) {
          e.preventDefault();
          lastEl.focus();
        }
      } else {
        if (document.activeElement === lastEl) {
          e.preventDefault();
          firstEl.focus();
        }
      }
    });
  }

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
    if (total <= 1) {
      var nextB = document.getElementById('hcNext');
      var prevB = document.getElementById('hcPrev');
      var dotsW = document.getElementById('hcDots');
      if (nextB) nextB.style.display = 'none';
      if (prevB) prevB.style.display = 'none';
      if (dotsW) dotsW.style.display = 'none';
      return;
    }

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
    var countNum = document.getElementById('venueCountNum');
    var activeChips = document.getElementById('venueActiveChips');
    var resetLink = document.getElementById('venueResetLink');

    if (!cityFilts.length && !cards.length) return;

    // Read initial URL params if present
    var urlParams = new URLSearchParams(window.location.search);
    var curCity = urlParams.get('city') || 'all';
    var curType = urlParams.get('type') || 'all';
    var curPrice = urlParams.get('price') || 'all';
    var curFeatures = [];
    if (urlParams.get('features')) {
      curFeatures = urlParams.get('features').split(',').map(function (s) { return s.trim().toLowerCase(); }).filter(Boolean);
    }

    function syncUrl() {
      var params = new URLSearchParams();
      if (curCity !== 'all') params.set('city', curCity);
      if (curType !== 'all') params.set('type', curType);
      if (curPrice !== 'all') params.set('price', curPrice);
      if (curFeatures.length) params.set('features', curFeatures.join(','));
      var query = params.toString();
      var newUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
      try {
        window.history.replaceState(null, '', newUrl);
      } catch (err) {}
    }

    function updateActiveChips() {
      if (!activeChips) return;
      activeChips.innerHTML = '';
      var hasFilter = false;

      function addChip(label, onRemove) {
        hasFilter = true;
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'active-filter-chip';
        chip.innerHTML = label + ' <span class="chip-x" aria-hidden="true">✕</span>';
        chip.setAttribute('aria-label', 'Удалить фильтр ' + label);
        chip.addEventListener('click', onRemove);
        activeChips.appendChild(chip);
      }

      if (curCity !== 'all') {
        var activeCityBtn = document.querySelector('#cityFilters .filter[data-city="' + curCity + '"]');
        var cityName = activeCityBtn ? activeCityBtn.textContent.replace(/\s*\(\d+\)/, '').trim() : curCity;
        addChip(cityName, function () {
          setCity('all');
        });
      }

      if (curType !== 'all') {
        addChip(curType.charAt(0).toUpperCase() + curType.slice(1), function () {
          curType = 'all';
          if (extra) {
            extra.querySelectorAll('.tag[data-filter-type]').forEach(function (x) {
              x.classList.remove('active');
              x.setAttribute('aria-pressed', 'false');
            });
          }
          applyVenues();
        });
      }

      if (curPrice !== 'all') {
        addChip(curPrice, function () {
          curPrice = 'all';
          if (extra) {
            extra.querySelectorAll('.tag[data-filter-price]').forEach(function (x) {
              x.classList.remove('active');
              x.setAttribute('aria-pressed', 'false');
            });
          }
          applyVenues();
        });
      }

      curFeatures.forEach(function (feat) {
        var featBtn = extra ? extra.querySelector('.tag[data-filter-feature="' + feat + '"]') : null;
        var featLabel = featBtn ? featBtn.textContent.trim() : feat;
        addChip(featLabel, function () {
          curFeatures = curFeatures.filter(function (f) { return f !== feat; });
          if (featBtn) {
            featBtn.classList.remove('active');
            featBtn.setAttribute('aria-pressed', 'false');
          }
          applyVenues();
        });
      });

      if (resetLink) {
        resetLink.style.display = hasFilter ? 'inline-flex' : 'none';
      }
    }

    function applyVenues() {
      var count = 0;
      cards = document.querySelectorAll('.venue-card');
      cards.forEach(function (card) {
        var cardCity = card.getAttribute('data-city');
        var cardType = (card.getAttribute('data-type') || '').toLowerCase();
        var cardPrice = card.getAttribute('data-price');
        var cardFeatures = (card.getAttribute('data-features') || '').toLowerCase();

        var matchCity = (curCity === 'all' || cardCity === curCity);
        var matchType = (curType === 'all' || cardType.indexOf(curType) !== -1);
        var matchPrice = (curPrice === 'all' || cardPrice === curPrice);

        var matchFeatures = true;
        if (curFeatures.length > 0) {
          for (var i = 0; i < curFeatures.length; i++) {
            if (cardFeatures.indexOf(curFeatures[i]) === -1) {
              matchFeatures = false;
              break;
            }
          }
        }

        var show = matchCity && matchType && matchPrice && matchFeatures;
        card.style.display = show ? '' : 'none';
        if (show) count++;
      });

      if (venueEmpty) {
        venueEmpty.classList.toggle('show', count === 0);
      }
      if (countNum) {
        countNum.textContent = count;
      }
      updateActiveChips();
      syncUrl();
    }

    function setCity(city) {
      curCity = city;
      cityFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-city') === city);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      applyVenues();
    }

    cityFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        setCity(b.getAttribute('data-city'));
      });
    });

    function resetAllVenues() {
      curCity = 'all';
      curType = 'all';
      curPrice = 'all';
      curFeatures = [];
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
    }

    if (venueReset) venueReset.addEventListener('click', resetAllVenues);
    if (resetLink) resetLink.addEventListener('click', resetAllVenues);

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

      // Multi-select features!
      extra.querySelectorAll('.tag[data-filter-feature]').forEach(function (t) {
        t.addEventListener('click', function () {
          var val = (t.getAttribute('data-filter-feature') || '').toLowerCase();
          var idx = curFeatures.indexOf(val);
          if (idx !== -1) {
            curFeatures.splice(idx, 1);
            t.classList.remove('active');
            t.setAttribute('aria-pressed', 'false');
          } else {
            curFeatures.push(val);
            t.classList.add('active');
            t.setAttribute('aria-pressed', 'true');
          }
          applyVenues();
        });
      });
    }

    // Apply URL params on init
    if (curCity !== 'all') {
      var cBtn = document.querySelector('#cityFilters .filter[data-city="' + curCity + '"]');
      if (cBtn) {
        cityFilts.forEach(function (x) { x.classList.remove('active'); x.setAttribute('aria-pressed', 'false'); });
        cBtn.classList.add('active');
        cBtn.setAttribute('aria-pressed', 'true');
      }
    }
    if (extra) {
      if (curType !== 'all') {
        var tBtn = extra.querySelector('.tag[data-filter-type="' + curType + '"]');
        if (tBtn) { tBtn.classList.add('active'); tBtn.setAttribute('aria-pressed', 'true'); if (moreBtn && !extra.classList.contains('open')) moreBtn.click(); }
      }
      if (curPrice !== 'all') {
        var pBtn = extra.querySelector('.tag[data-filter-price="' + curPrice + '"]');
        if (pBtn) { pBtn.classList.add('active'); pBtn.setAttribute('aria-pressed', 'true'); if (moreBtn && !extra.classList.contains('open')) moreBtn.click(); }
      }
      curFeatures.forEach(function (f) {
        var fBtn = extra.querySelector('.tag[data-filter-feature="' + f + '"]');
        if (fBtn) { fBtn.classList.add('active'); fBtn.setAttribute('aria-pressed', 'true'); if (moreBtn && !extra.classList.contains('open')) moreBtn.click(); }
      });
    }
    applyVenues();
  })();

  // 8. EVENTS POSTER FILTERING (API Культура.РФ)
  (function initEventsFilters() {
    var cityFilts = document.querySelectorAll('#cityFilters .filter[data-city]');
    var dateFilts = document.querySelectorAll('#dateFilters .filter[data-when]');
    var cards = document.querySelectorAll('.event-card');
    var eventEmpty = document.getElementById('eventEmpty');
    var eventReset = document.getElementById('eventEmptyReset');
    var countNum = document.getElementById('eventCountNum');
    var activeChips = document.getElementById('eventActiveChips');
    var resetLink = document.getElementById('eventResetLink');

    if (!dateFilts.length) return;

    var urlParams = new URLSearchParams(window.location.search);
    var curCity = urlParams.get('city') || 'all';
    var curWhen = urlParams.get('when') || 'all';

    function syncUrl() {
      var params = new URLSearchParams();
      if (curCity !== 'all') params.set('city', curCity);
      if (curWhen !== 'all') params.set('when', curWhen);
      var query = params.toString();
      var newUrl = window.location.pathname + (query ? '?' + query : '') + window.location.hash;
      try {
        window.history.replaceState(null, '', newUrl);
      } catch (err) {}
    }

    function updateActiveChips() {
      if (!activeChips) return;
      activeChips.innerHTML = '';
      var hasFilter = false;

      function addChip(label, onRemove) {
        hasFilter = true;
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'active-filter-chip';
        chip.innerHTML = label + ' <span class="chip-x" aria-hidden="true">✕</span>';
        chip.setAttribute('aria-label', 'Удалить фильтр ' + label);
        chip.addEventListener('click', onRemove);
        activeChips.appendChild(chip);
      }

      if (curWhen !== 'all') {
        var whenBtn = document.querySelector('#dateFilters .filter[data-when="' + curWhen + '"]');
        var whenName = whenBtn ? whenBtn.textContent.trim() : curWhen;
        addChip(whenName, function () {
          setWhen('all');
        });
      }

      if (curCity !== 'all') {
        var cityBtn = document.querySelector('#cityFilters .filter[data-city="' + curCity + '"]');
        var cityName = cityBtn ? cityBtn.textContent.replace(/\s*\(\d+\)/, '').trim() : curCity;
        addChip(cityName, function () {
          setCity('all');
        });
      }

      if (resetLink) {
        resetLink.style.display = hasFilter ? 'inline-flex' : 'none';
      }
    }

    function applyEvents() {
      var count = 0;
      cards = document.querySelectorAll('.event-card');
      cards.forEach(function (c) {
        var cCity = c.getAttribute('data-city') || '';
        var cWhen = c.getAttribute('data-when') || '';
        var whenList = cWhen.split(/\s+/);

        var matchCity = (curCity === 'all' || cCity === curCity);
        var matchWhen = (curWhen === 'all' || whenList.indexOf(curWhen) !== -1);
        var show = matchCity && matchWhen;

        c.style.display = show ? '' : 'none';
        if (show) count++;
      });

      if (eventEmpty) {
        eventEmpty.classList.toggle('show', count === 0);
      }
      if (countNum) {
        countNum.textContent = count;
      }
      updateActiveChips();
      syncUrl();
    }

    function setCity(city) {
      curCity = city;
      cityFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-city') === city);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      applyEvents();
    }

    function setWhen(when) {
      curWhen = when;
      dateFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-when') === when);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      applyEvents();
    }

    cityFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        setCity(b.getAttribute('data-city'));
      });
    });

    dateFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        setWhen(b.getAttribute('data-when'));
      });
    });

    function resetAllEvents() {
      setCity('all');
      setWhen('all');
    }

    if (eventReset) eventReset.addEventListener('click', resetAllEvents);
    if (resetLink) resetLink.addEventListener('click', resetAllEvents);

    // Apply URL params on init
    if (curCity !== 'all') setCity(curCity);
    if (curWhen !== 'all') setWhen(curWhen);
    applyEvents();
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

  // 10. TOP PERIOD SWITCHER (Читают сейчас: сегодня, вчера, неделя, месяц)
  (function initTopPeriodSwitcher() {
    var navs = document.querySelectorAll('.top-period-nav');
    if (!navs.length) return;

    navs.forEach(function (nav) {
      var btns = nav.querySelectorAll('.top-period-btn');
      var container = nav.closest('aside') || nav.parentElement;
      btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var period = this.getAttribute('data-period');
          btns.forEach(function (b) {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
          });
          this.classList.add('active');
          this.setAttribute('aria-selected', 'true');

          if (container) {
            var lists = container.querySelectorAll('.top-list');
            lists.forEach(function (list) {
              if (list.getAttribute('data-period') === period) {
                list.style.display = 'flex';
              } else if (list.getAttribute('data-period')) {
                list.style.display = 'none';
              }
            });
          }
        });
      });
    });
  })();

  // 11. PHOTO STORY LIGHTBOX (Просмотр снимка крупно по клику)
  (function initPhotoLightbox() {
    var figures = document.querySelectorAll('.article-body figure.wp-block-image, .article-body .photo-story-figure');
    if (!figures.length) return;

    var lightbox = document.createElement('div');
    lightbox.className = 'photo-lightbox';
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.setAttribute('aria-label', 'Просмотр фотографии');
    lightbox.innerHTML = '<div class="photo-lightbox-inner">' +
      '<button type="button" class="photo-lightbox-close" aria-label="Закрыть">✕</button>' +
      '<img class="photo-lightbox-img" src="" alt="" />' +
      '<div class="photo-lightbox-cap"></div>' +
      '</div>';
    document.body.appendChild(lightbox);

    var lbImg = lightbox.querySelector('.photo-lightbox-img');
    var lbCap = lightbox.querySelector('.photo-lightbox-cap');
    var lbClose = lightbox.querySelector('.photo-lightbox-close');

    function closeLb() {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
    }

    if (lbClose) lbClose.addEventListener('click', closeLb);
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLb();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && lightbox.classList.contains('open')) closeLb();
    });

    figures.forEach(function (fig) {
      var img = fig.querySelector('img');
      if (!img) return;
      fig.classList.add('photo-story-figure');
      fig.addEventListener('click', function (e) {
        if (e.target.tagName && e.target.tagName.toLowerCase() === 'a') return;
        var cap = fig.querySelector('figcaption');
        lbImg.src = img.currentSrc || img.src;
        lbImg.alt = img.alt || '';
        lbCap.textContent = cap ? cap.textContent : '';
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
      });
    });
  })();

  // 12. IN-PLACE LOAD MORE (Главная: подгрузка без перехода на другую страницу)
  (function initLoadMore() {
    var btn = document.getElementById('loadMoreBtn');
    var list = document.getElementById('newsFeedList') || document.getElementById('newsList') || document.querySelector('.news-list');
    if (!btn || !list) return;

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var curPage = parseInt(btn.getAttribute('data-page') || '1', 10);
      var maxPage = parseInt(btn.getAttribute('data-max') || '5', 10);
      var nextPage = curPage + 1;

      btn.classList.add('loading');
      var origContent = btn.innerHTML;
      btn.innerHTML = '<span>Загрузка…</span>';

      var endpoint = '/wp-json/wp/v2/posts?page=' + nextPage + '&per_page=6&_embed';
      fetch(endpoint)
        .then(function (res) {
          if (!res.ok) throw new Error('API request failed');
          return res.json();
        })
        .then(function (posts) {
          btn.classList.remove('loading');
          btn.innerHTML = origContent;
          if (Array.isArray(posts) && posts.length > 0) {
            posts.forEach(function (p) {
              var card = document.createElement('article');
              card.className = 'news-card rev on';
              var title = p.title ? (p.title.rendered || p.title) : '';
              var link = p.link || '#';
              var excerpt = p.excerpt ? (p.excerpt.rendered || p.excerpt).replace(/<[^>]+>/g, '') : '';
              var imgUrl = '';
              if (p._embedded && p._embedded['wp:featuredmedia'] && p._embedded['wp:featuredmedia'][0]) {
                imgUrl = p._embedded['wp:featuredmedia'][0].source_url;
              }
              card.innerHTML = 
                '<div class="nc-img">' +
                  (imgUrl ? '<img src="' + imgUrl + '" alt="" loading="lazy" />' : '<div class="ph-img" style="aspect-ratio:3/2"></div>') +
                '</div>' +
                '<div class="nc-body">' +
                  '<h3 class="nc-title"><a href="' + link + '" class="card-permalink">' + title + '</a></h3>' +
                  '<p class="nc-excerpt">' + excerpt.slice(0, 110) + (excerpt.length > 110 ? '…' : '') + '</p>' +
                  '<div class="nc-meta">Сегодня</div>' +
                '</div>';
              list.appendChild(card);
            });
            btn.setAttribute('data-page', nextPage);
            if (nextPage >= maxPage) {
              btn.style.display = 'none';
            }
          } else {
            btn.style.display = 'none';
          }
        })
        .catch(function () {
          // Graceful in-place fallback (static prototype or offline)
          btn.classList.remove('loading');
          btn.innerHTML = origContent;
          var samples = list.querySelectorAll('.news-card:not(.featured)');
          if (samples.length > 0) {
            for (var i = 0; i < Math.min(3, samples.length); i++) {
              var cl = samples[i].cloneNode(true);
              cl.classList.add('on');
              list.appendChild(cl);
            }
            btn.setAttribute('data-page', nextPage);
            if (nextPage >= maxPage) {
              btn.style.display = 'none';
            }
          } else {
            btn.style.display = 'none';
          }
        });
    });
  })();

})();
