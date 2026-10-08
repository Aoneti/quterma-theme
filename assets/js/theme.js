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
    mm.hidden = false;
    mm.removeAttribute('hidden');
    mm.removeAttribute('inert');
    if ('inert' in mm) mm.inert = false;
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
    mm.hidden = true;
    mm.setAttribute('hidden', '');
    mm.setAttribute('inert', '');
    if ('inert' in mm) mm.inert = true;
    if (mo) mo.classList.remove('open');
    if (burgerBtn) burgerBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';

    if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
      previouslyFocusedElement.focus();
    } else if (burgerBtn) {
      burgerBtn.focus();
    }
  }

  // Ensure closed state initially
  if (mm && !mm.classList.contains('open')) {
    mm.hidden = true;
    mm.setAttribute('hidden', '');
    mm.setAttribute('inert', '');
    if ('inert' in mm) mm.inert = true;
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
    function start() {
      stop();
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
      }
      timer = setInterval(next, 7000);
    }
    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    var nextBtn = document.getElementById('hcNext');
    var prevBtn = document.getElementById('hcPrev');

    if (nextBtn) {
      nextBtn.addEventListener('click', function () { next(); start(); });
    }
    if (prevBtn) {
      prevBtn.addEventListener('click', function () { prev(); start(); });
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        go(parseInt(dot.getAttribute('data-i'), 10));
        start();
      });
    });

    var wrapEl = document.getElementById('heroCarousel');
    if (wrapEl) {
      wrapEl.addEventListener('mouseenter', stop);
      wrapEl.addEventListener('mouseleave', start);
      wrapEl.addEventListener('focusin', stop);
      wrapEl.addEventListener('focusout', function (e) {
        if (!wrapEl.contains(e.relatedTarget)) {
          start();
        }
      });

      // Swipe support for touch devices
      var touchStartX = 0;
      var touchDiffX = 0;
      var isSwiping = false;

      wrapEl.addEventListener('touchstart', function (e) {
        stop();
        if (e.touches && e.touches.length === 1) {
          touchStartX = e.touches[0].clientX;
          touchDiffX = 0;
          isSwiping = true;
        }
      }, { passive: true });

      wrapEl.addEventListener('touchmove', function (e) {
        if (!isSwiping || !e.touches || e.touches.length !== 1) return;
        touchDiffX = e.touches[0].clientX - touchStartX;
      }, { passive: true });

      wrapEl.addEventListener('touchend', function () {
        if (isSwiping) {
          if (Math.abs(touchDiffX) > 40) {
            if (touchDiffX < 0) {
              next();
            } else {
              prev();
            }
          }
          isSwiping = false;
        }
        start();
      }, { passive: true });
    }

    start();
  })();

  // 6.5. FILTER SLIDERS (desktop buttons + touch scroll + drag to scroll + mouse wheel)
  function initFilterSliders() {
    // Auto-wrap any standalone .filters that are not already inside .filters-slider-wrap
    document.querySelectorAll('.filters').forEach(function (track) {
      if (!track.parentElement.classList.contains('filters-slider-wrap')) {
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
    filterWraps.forEach(function (wrap) {
      if (wrap.dataset.sliderInit) return;
      wrap.dataset.sliderInit = 'true';

      var track = wrap.querySelector('.filters');
      var prev = wrap.querySelector('.filter-scroll-prev');
      var next = wrap.querySelector('.filter-scroll-next');
      if (!track) return;

      function updateBtns() {
        var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
        if (prev) prev.classList.toggle('vis', track.scrollLeft > 4);
        if (next) next.classList.toggle('vis', maxScroll > 4 && track.scrollLeft < maxScroll - 4);
      }

      if (prev) {
        prev.addEventListener('click', function (e) {
          e.preventDefault();
          track.scrollBy({ left: -240, behavior: 'smooth' });
          setTimeout(updateBtns, 200);
        });
      }
      if (next) {
        next.addEventListener('click', function (e) {
          e.preventDefault();
          track.scrollBy({ left: 240, behavior: 'smooth' });
          setTimeout(updateBtns, 200);
        });
      }

      track.addEventListener('scroll', updateBtns, { passive: true });
      window.addEventListener('resize', updateBtns, { passive: true });
      [50, 150, 300, 600, 1200].forEach(function (delay) {
        setTimeout(updateBtns, delay);
      });
      if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(updateBtns);
      }

      // Mouse drag-to-scroll for desktop with drag-vs-click threshold
      var isDown = false, startX = 0, scrollLeftVal = 0, hasMoved = false;
      track.addEventListener('mousedown', function (e) {
        if (e.button !== 0) return;
        isDown = true;
        hasMoved = false;
        startX = e.pageX - track.offsetLeft;
        scrollLeftVal = track.scrollLeft;
      });
      track.addEventListener('mouseleave', function () { isDown = false; });
      window.addEventListener('mouseup', function () {
        if (isDown) {
          isDown = false;
          setTimeout(function () { hasMoved = false; }, 50);
        }
      });
      track.addEventListener('mousemove', function (e) {
        if (!isDown) return;
        var x = e.pageX - track.offsetLeft;
        var walk = (x - startX) * 1.5;
        if (Math.abs(walk) > 4) {
          hasMoved = true;
        }
        e.preventDefault();
        track.scrollLeft = scrollLeftVal - walk;
        updateBtns();
      });

      // Suppress filter click if dragged
      track.addEventListener('click', function (e) {
        if (hasMoved) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);

      // Auto-center active button on init
      var activeBtn = track.querySelector('.filter.active');
      if (activeBtn && activeBtn.offsetLeft > track.clientWidth / 2) {
        track.scrollLeft = activeBtn.offsetLeft - (track.clientWidth - activeBtn.clientWidth) / 2;
        setTimeout(updateBtns, 100);
      }
    });
  }
  initFilterSliders();

  // 7. GASTROGUIDE FILTERING (Города, Кухня, Цена, Особенности)
  (function initGastroguideFilters() {
    var venueGrid = document.getElementById('venueGrid');
    if (!venueGrid) return;

    var cityFilts = document.querySelectorAll('#cityFilters .filter[data-city]');
    var cards = venueGrid.querySelectorAll('.venue-card');
    var venueEmpty = document.getElementById('venueEmpty');
    var venueReset = document.getElementById('venueEmptyReset');
    var moreBtn = document.getElementById('moreFiltersBtn');
    var extra = document.getElementById('extraFilters');
    var countNum = document.getElementById('venueCountNum');
    var activeChips = document.getElementById('venueActiveChips');
    var resetLink = document.getElementById('venueResetLink');

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
      var params = new URLSearchParams(window.location.search);
      if (curCity !== 'all') {
        params.set('city', curCity);
      } else {
        params.delete('city');
      }
      if (curType !== 'all') {
        params.set('type', curType);
      } else {
        params.delete('type');
      }
      if (curPrice !== 'all') {
        params.set('price', curPrice);
      } else {
        params.delete('price');
      }
      if (curFeatures.length > 0) {
        params.set('features', curFeatures.join(','));
      } else {
        params.delete('features');
      }
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
          setCity('all', true);
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
          applyVenues(true);
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
          applyVenues(true);
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
          applyVenues(true);
        });
      });

      if (resetLink) {
        resetLink.style.display = hasFilter ? 'inline-flex' : 'none';
      }
    }

    function applyVenues(isUserAction) {
      var count = 0;
      cards = venueGrid.querySelectorAll('.venue-card');
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
      if (isUserAction) {
        syncUrl();
      }
    }

    function setCity(city, isUserAction) {
      curCity = city;
      cityFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-city') === city);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      applyVenues(isUserAction);
    }

    cityFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        setCity(b.getAttribute('data-city'), true);
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
      applyVenues(true);
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
          applyVenues(true);
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
          applyVenues(true);
        });
      });

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
          applyVenues(true);
        });
      });
    }

    // Apply URL params on init without rewriting history
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
    applyVenues(false);
  })();

  // 8. EVENTS POSTER FILTERING (API Культура.РФ)
  (function initEventsFilters() {
    var dateFilts = document.querySelectorAll('#dateFilters .filter[data-when]');
    var cityFilts = document.querySelectorAll('#cityFilters .filter[data-city]');
    var cards = document.querySelectorAll('.event-card');
    var eventEmpty = document.getElementById('eventEmpty');
    var eventReset = document.getElementById('eventEmptyReset');
    var countNum = document.getElementById('eventCountNum');
    var activeChips = document.getElementById('eventActiveChips');
    var resetLink = document.getElementById('eventResetLink');

    if (!dateFilts.length && !document.getElementById('eventsList') && !document.querySelector('.events-grid')) return;

    var urlParams = new URLSearchParams(window.location.search);
    var curCity = urlParams.get('city') || 'all';
    var curWhen = urlParams.get('when') || 'all';

    function syncUrl() {
      var params = new URLSearchParams(window.location.search);
      if (curCity !== 'all') {
        params.set('city', curCity);
      } else {
        params.delete('city');
      }
      if (curWhen !== 'all') {
        params.set('when', curWhen);
      } else {
        params.delete('when');
      }
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
          setWhen('all', true);
        });
      }

      if (curCity !== 'all') {
        var cityBtn = document.querySelector('#cityFilters .filter[data-city="' + curCity + '"]');
        var cityName = cityBtn ? cityBtn.textContent.replace(/\s*\(\d+\)/, '').trim() : curCity;
        addChip(cityName, function () {
          setCity('all', true);
        });
      }

      if (resetLink) {
        resetLink.style.display = hasFilter ? 'inline-flex' : 'none';
      }
    }

    function applyEvents(isUserAction) {
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
      if (isUserAction) {
        syncUrl();
      }
    }

    function setCity(city, isUserAction) {
      curCity = city;
      cityFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-city') === city);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      applyEvents(isUserAction);
    }

    function setWhen(when, isUserAction) {
      curWhen = when;
      dateFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-when') === when);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
      applyEvents(isUserAction);
    }

    cityFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        setCity(b.getAttribute('data-city'), true);
      });
    });

    dateFilts.forEach(function (b) {
      b.addEventListener('click', function () {
        setWhen(b.getAttribute('data-when'), true);
      });
    });

    function resetAllEvents() {
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
      applyEvents(true);
    }

    if (eventReset) eventReset.addEventListener('click', resetAllEvents);
    if (resetLink) resetLink.addEventListener('click', resetAllEvents);

    // Apply URL params on init without rewriting history
    if (curCity !== 'all') {
      cityFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-city') === curCity);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }
    if (curWhen !== 'all') {
      dateFilts.forEach(function (x) {
        var isActive = (x.getAttribute('data-when') === curWhen);
        x.classList.toggle('active', isActive);
        x.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }
    applyEvents(false);
  })();

  // 9. FOOTER MARQUEE SEAMLESS LOOP
  (function initMarquee() {
    var track = document.getElementById('footMarquee');
    if (!track || !track.children.length) return;

    var text = track.children[0].textContent;
    var PX_PER_SEC = 105;
    var MIN_COPIES = 8;

    function rebuild() {
      track.style.animation = 'none';
      track.innerHTML = '<span>' + text + '</span>';
      var spanWidth = track.children[0].getBoundingClientRect().width;
      if (!spanWidth) {
        track.style.animation = '';
        return;
      }
      var viewportW = window.innerWidth;
      var needed = Math.ceil((viewportW * 2.4) / spanWidth);
      if (needed % 2 !== 0) needed += 1;
      needed = Math.max(needed, MIN_COPIES);

      var html = '<span>' + text + '</span>';
      for (var i = 1; i < needed; i++) {
        html += '<span aria-hidden="true">' + text + '</span>';
      }
      track.innerHTML = html;
      void track.offsetWidth; // force reflow

      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        track.style.animation = 'none';
        return;
      }

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
      var btns = Array.prototype.slice.call(nav.querySelectorAll('.top-period-btn'));
      var container = nav.closest('aside') || nav.parentElement;

      function selectTab(btnToSelect) {
        var period = btnToSelect.getAttribute('data-period');
        btns.forEach(function (b) {
          var isCurrent = (b === btnToSelect);
          b.classList.toggle('active', isCurrent);
          b.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
          b.setAttribute('tabindex', isCurrent ? '0' : '-1');
        });

        if (container) {
          var lists = container.querySelectorAll('.top-list');
          lists.forEach(function (list) {
            var match = (list.getAttribute('data-period') === period);
            if (match) {
              list.style.display = 'flex';
              list.removeAttribute('hidden');
            } else if (list.getAttribute('data-period')) {
              list.style.display = 'none';
              list.setAttribute('hidden', '');
            }
          });
        }
      }

      btns.forEach(function (btn, index) {
        btn.addEventListener('click', function () {
          selectTab(this);
        });

        // WAI-ARIA tab keyboard navigation
        btn.addEventListener('keydown', function (e) {
          var newIndex = -1;
          if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            newIndex = (index + 1) % btns.length;
          } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            newIndex = (index - 1 + btns.length) % btns.length;
          } else if (e.key === 'Home') {
            newIndex = 0;
          } else if (e.key === 'End') {
            newIndex = btns.length - 1;
          }

          if (newIndex !== -1) {
            e.preventDefault();
            btns[newIndex].focus();
            selectTab(btns[newIndex]);
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
    lightbox.hidden = true;
    lightbox.setAttribute('hidden', '');
    if ('inert' in lightbox) lightbox.inert = true;
    lightbox.setAttribute('inert', '');

    lightbox.innerHTML = '<div class="photo-lightbox-inner">' +
      '<button type="button" class="photo-lightbox-close" aria-label="Закрыть">✕</button>' +
      '<img class="photo-lightbox-img" src="" alt="" />' +
      '<div class="photo-lightbox-cap"></div>' +
      '</div>';
    document.body.appendChild(lightbox);

    var lbImg = lightbox.querySelector('.photo-lightbox-img');
    var lbCap = lightbox.querySelector('.photo-lightbox-cap');
    var lbClose = lightbox.querySelector('.photo-lightbox-close');
    var previouslyFocused = null;

    function getOriginalImageUrl(img, fig) {
      if (!img) return '';
      // Check parent <a> linking directly to image file
      var parentLink = fig.querySelector('a');
      if (parentLink && parentLink.href && /\.(jpe?g|png|webp|avif|gif)$/i.test(parentLink.href)) {
        return parentLink.href;
      }
      // Check full-size data attributes commonly used by WordPress
      var dataFull = img.getAttribute('data-full-url') ||
                     img.getAttribute('data-orig-file') ||
                     img.getAttribute('data-large-file') ||
                     img.getAttribute('data-original');
      if (dataFull) return dataFull;

      // Check srcset for highest resolution candidate
      var srcset = img.getAttribute('srcset');
      if (srcset) {
        var candidates = srcset.split(',').map(function (s) {
          var parts = s.trim().split(/\s+/);
          var url = parts[0];
          var descriptor = parts[1] || '1x';
          var width = parseInt(descriptor, 10);
          return { url: url, width: isNaN(width) ? 0 : width };
        });
        candidates.sort(function (a, b) { return b.width - a.width; });
        if (candidates.length && candidates[0].url) {
          return candidates[0].url;
        }
      }

      // Fallback to src (not thumbnail currentSrc)
      return img.src || img.currentSrc || '';
    }

    function openLb(src, alt, capText) {
      previouslyFocused = document.activeElement;
      lbImg.src = src;
      lbImg.alt = alt || '';
      lbCap.textContent = capText || '';

      lightbox.removeAttribute('hidden');
      lightbox.removeAttribute('inert');
      if ('inert' in lightbox) lightbox.inert = false;
      lightbox.hidden = false;
      lightbox.classList.add('open');
      document.body.style.overflow = 'hidden';

      setTimeout(function () {
        if (lbClose) lbClose.focus();
      }, 50);
    }

    function closeLb() {
      lightbox.classList.remove('open');
      lightbox.hidden = true;
      lightbox.setAttribute('hidden', '');
      if ('inert' in lightbox) lightbox.inert = true;
      lightbox.setAttribute('inert', '');
      document.body.style.overflow = '';
      lbImg.src = '';

      if (previouslyFocused && typeof previouslyFocused.focus === 'function') {
        previouslyFocused.focus();
      }
    }

    if (lbClose) lbClose.addEventListener('click', closeLb);
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLb();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && lightbox.classList.contains('open')) closeLb();
    });
    lightbox.addEventListener('keydown', function (e) {
      if (e.key === 'Tab') {
        e.preventDefault();
        if (lbClose) lbClose.focus();
      }
    });

    figures.forEach(function (fig) {
      var img = fig.querySelector('img');
      if (!img) return;
      fig.classList.add('photo-story-figure');
      fig.addEventListener('click', function (e) {
        if (e.target.tagName && e.target.tagName.toLowerCase() === 'a') return;
        var cap = fig.querySelector('figcaption');
        var originalSrc = getOriginalImageUrl(img, fig);
        openLb(originalSrc, img.alt, cap ? cap.textContent : '');
      });
    });
  })();

  // Helper to format ISO date string into Russian human-readable format
  function formatPostDate(isoStr) {
    if (!isoStr) return '';
    var d = new Date(isoStr);
    if (isNaN(d.getTime())) return '';

    var months = [
      'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
      'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
    ];

    var now = new Date();
    var isSameDay = (d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate());

    var hours = String(d.getHours());
    if (hours.length < 2) hours = '0' + hours;
    var mins = String(d.getMinutes());
    if (mins.length < 2) mins = '0' + mins;
    var timeStr = hours + ':' + mins;

    if (isSameDay) {
      return 'Сегодня, ' + timeStr;
    }

    var yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    if (d.getFullYear() === yesterday.getFullYear() && d.getMonth() === yesterday.getMonth() && d.getDate() === yesterday.getDate()) {
      return 'Вчера, ' + timeStr;
    }

    var day = d.getDate();
    var month = months[d.getMonth()];
    if (d.getFullYear() === now.getFullYear()) {
      return day + ' ' + month + ', ' + timeStr;
    }
    return day + ' ' + month + ' ' + d.getFullYear() + ', ' + timeStr;
  }

  // 12. IN-PLACE LOAD MORE (Главная: подгрузка без перехода на другую страницу)
  (function initLoadMore() {
    var btn = document.getElementById('loadMoreBtn');
    var list = document.getElementById('newsFeedList') || document.getElementById('newsList') || document.querySelector('.news-list');
    if (!btn || !list) return;

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      if (btn.classList.contains('loading')) return;

      var curPage = parseInt(btn.getAttribute('data-page') || '1', 10);
      var maxPage = parseInt(btn.getAttribute('data-max') || '5', 10);
      var nextPage = curPage + 1;

      var perPage = 6;
      if (window.qutermaSettings && window.qutermaSettings.perPage) {
        perPage = parseInt(window.qutermaSettings.perPage, 10);
      } else if (btn.getAttribute('data-per-page')) {
        perPage = parseInt(btn.getAttribute('data-per-page'), 10);
      }

      // Collect already displayed post IDs to exclude duplicates
      var excludeIds = [];
      var rawExclude = btn.getAttribute('data-exclude');
      if (rawExclude) {
        rawExclude.split(',').forEach(function (idStr) {
          var id = parseInt(idStr.trim(), 10);
          if (id && excludeIds.indexOf(id) === -1) excludeIds.push(id);
        });
      }
      document.querySelectorAll('[data-post-id], [data-id]').forEach(function (el) {
        var id = parseInt(el.getAttribute('data-post-id') || el.getAttribute('data-id'), 10);
        if (id && excludeIds.indexOf(id) === -1) {
          excludeIds.push(id);
        }
      });

      btn.classList.add('loading');
      var origContent = btn.innerHTML;
      btn.innerHTML = '<span>Загрузка…</span>';

      // Hide any previous error message
      var prevErr = document.getElementById('loadMoreError');
      if (prevErr) {
        prevErr.style.display = 'none';
      }

      var baseEndpoint = (window.qutermaSettings && window.qutermaSettings.postsRestUrl)
        ? window.qutermaSettings.postsRestUrl
        : (btn.getAttribute('data-endpoint') || '/wp-json/wp/v2/posts');

      var url;
      try {
        url = new URL(baseEndpoint, window.location.href);
      } catch (err) {
        url = new URL(baseEndpoint, window.location.origin);
      }

      url.searchParams.set('page', nextPage);
      url.searchParams.set('per_page', perPage);
      url.searchParams.set('_embed', '1');
      if (excludeIds.length > 0) {
        url.searchParams.set('exclude', excludeIds.join(','));
      }

      var headers = {};
      if (window.qutermaSettings && window.qutermaSettings.nonce) {
        headers['X-WP-Nonce'] = window.qutermaSettings.nonce;
      }

      fetch(url.toString(), { headers: headers })
        .then(function (res) {
          if (!res.ok) {
            var err = new Error('HTTP ' + res.status);
            err.status = res.status;
            throw err;
          }
          var totalPagesHeader = res.headers.get('X-WP-TotalPages');
          if (totalPagesHeader) {
            var tp = parseInt(totalPagesHeader, 10);
            if (!isNaN(tp) && tp > 0) {
              maxPage = tp;
              btn.setAttribute('data-max', maxPage);
            }
          }
          return res.json();
        })
        .then(function (posts) {
          btn.classList.remove('loading');
          btn.innerHTML = origContent;

          if (Array.isArray(posts) && posts.length > 0) {
            var addedCount = 0;
            posts.forEach(function (p) {
              // Ensure uniqueness: skip if this post is already rendered
              if (p.id && excludeIds.indexOf(p.id) !== -1) {
                return;
              }
              if (p.id && document.querySelector('[data-post-id="' + p.id + '"], [data-id="' + p.id + '"]')) {
                return;
              }

              var card = document.createElement('article');
              card.className = 'news-card rev on';
              if (p.id) {
                card.setAttribute('data-id', p.id);
                card.setAttribute('data-post-id', p.id);
                excludeIds.push(p.id);
              }

              var title = p.title ? (p.title.rendered || p.title) : '';
              var link = p.link || '#';
              var excerpt = p.excerpt ? (p.excerpt.rendered || p.excerpt).replace(/<[^>]+>/g, '').trim() : '';
              var imgUrl = '';
              if (p._embedded && p._embedded['wp:featuredmedia'] && p._embedded['wp:featuredmedia'][0]) {
                imgUrl = p._embedded['wp:featuredmedia'][0].source_url || '';
              }

              var catName = '';
              if (p._embedded && p._embedded['wp:term'] && p._embedded['wp:term'][0] && p._embedded['wp:term'][0][0]) {
                catName = p._embedded['wp:term'][0][0].name || '';
              }

              var dateFormatted = formatPostDate(p.date);

              card.innerHTML =
                '<div class="nc-img">' +
                  (imgUrl ? '<img src="' + imgUrl + '" alt="" loading="lazy" />' : '<div class="ph-img" style="aspect-ratio:3/2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="38" height="38"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/></svg></div>') +
                '</div>' +
                '<div class="nc-body">' +
                  (catName ? '<span class="sr-only">' + catName + '</span>' : '') +
                  '<h3 class="nc-title"><a href="' + link + '" class="card-permalink">' + title + '</a></h3>' +
                  '<p class="nc-excerpt">' + excerpt.slice(0, 110) + (excerpt.length > 110 ? '…' : '') + '</p>' +
                  '<div class="nc-meta">' + (dateFormatted ? '<time datetime="' + (p.date || '') + '">' + dateFormatted + '</time>' : '') + '</div>' +
                '</div>';

              list.appendChild(card);
              addedCount++;
            });

            btn.setAttribute('data-page', nextPage);
            btn.setAttribute('data-exclude', excludeIds.join(','));

            if (nextPage >= maxPage || posts.length < perPage || addedCount === 0) {
              btn.style.display = 'none';
            }
          } else {
            btn.style.display = 'none';
          }
        })
        .catch(function () {
          btn.classList.remove('loading');
          btn.innerHTML = origContent;

          // Honestly display error message without cloning cards
          var errEl = document.getElementById('loadMoreError');
          if (!errEl) {
            errEl = document.createElement('div');
            errEl.id = 'loadMoreError';
            errEl.className = 'load-more-error';
            errEl.setAttribute('role', 'alert');
            btn.parentNode.insertBefore(errEl, btn.nextSibling);
          }
          errEl.textContent = 'Ошибка загрузки записей. Пожалуйста, попробуйте позже.';
          errEl.style.display = 'block';
        });
    });
  })();

  // 12. ASYNCHRONOUS POST VIEW TRACKING (Beacon / REST API)
  (function () {
    if (typeof qutermaSettings === 'undefined' || !qutermaSettings.trackViewUrl || !qutermaSettings.postId) {
      return;
    }
    var postId = parseInt(qutermaSettings.postId, 10);
    if (!postId || isNaN(postId) || postId <= 0) return;

    // Session debounce: do not spam if viewed in current tab within 30 minutes
    var storageKey = 'quterma_view_' + postId;
    try {
      var lastView = localStorage.getItem(storageKey);
      if (lastView && (Date.now() - parseInt(lastView, 10)) < 1800000) {
        return;
      }
      localStorage.setItem(storageKey, Date.now().toString());
    } catch (e) {}

    var payload = JSON.stringify({ post_id: postId });
    if (navigator.sendBeacon) {
      var blob = new Blob([payload], { type: 'application/json' });
      navigator.sendBeacon(qutermaSettings.trackViewUrl, blob);
    } else if (window.fetch) {
      fetch(qutermaSettings.trackViewUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': qutermaSettings.nonce || ''
        },
        body: payload,
        keepalive: true,
        credentials: 'same-origin'
      }).catch(function () {});
    }
  })();

})();
