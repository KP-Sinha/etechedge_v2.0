document.addEventListener("DOMContentLoaded", () => {
  // Sticky Navbar & Back to Top behavior
  const nav = document.getElementById("mainNav");
  const topBtn = document.getElementById("backTop");

  // Determine active page
  const page = location.pathname.split("/").pop() || "index.html";
  const key = page === "index.html" || page === "" ? "home" : page.replace(".html", "");

  const menu = document.getElementById("navMenu");
  const toggler = document.querySelector(".navbar-toggler");

  document.querySelectorAll(".nav-link, .nav-mobile-close").forEach((a) => {
    if (a.classList.contains("nav-link")) {
      a.classList.toggle("active", a.dataset.page === key);
    }
    a.addEventListener("click", () => {
      if (menu && menu.classList.contains("show")) {
        const bsCollapse = bootstrap.Collapse.getInstance(menu) || new bootstrap.Collapse(menu, { toggle: false });
        if (bsCollapse) bsCollapse.hide();
      }
    });
  });

  // Mobile menu: close on outside click
  document.addEventListener("click", (e) => {
    if (menu && menu.classList.contains("show")) {
      const isInside = menu.contains(e.target);
      const isToggler = toggler && toggler.contains(e.target);
      if (!isInside && !isToggler) {
        const bsCollapse = bootstrap.Collapse.getInstance(menu) || new bootstrap.Collapse(menu, { toggle: false });
        if (bsCollapse) bsCollapse.hide();
      }
    }
  });

  // Mobile menu: close on Escape key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && menu && menu.classList.contains("show")) {
      const bsCollapse = bootstrap.Collapse.getInstance(menu) || new bootstrap.Collapse(menu, { toggle: false });
      if (bsCollapse) bsCollapse.hide();
    }
  });

  function scrollUI() {
    const scrollY = window.scrollY || window.pageYOffset;
    nav?.classList.toggle("scrolled", scrollY > 20);
    topBtn?.classList.toggle("show", scrollY > 300);
  }
  window.addEventListener("scroll", scrollUI, { passive: true });
  scrollUI();

  topBtn?.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // Intersection Observer for scroll animations
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add("visible");
          observer.unobserve(e.target);
        }
      });
    },
    { threshold: 0.1 }
  );

  document
    .querySelectorAll(".fade-up, .service-card, .product-showcase-card, .career-card, .case-card-modern, .blog-card-modern, .contact-info-card")
    .forEach((el) => {
      if (!el.classList.contains("fade-up")) el.classList.add("fade-up");
      observer.observe(el);
    });

  // Hero Slider
  const slides = document.querySelectorAll(".hero-slide");
  const bgSlides = document.querySelectorAll(".hero-bg-slide");
  const dots = document.querySelectorAll(".hero-dot");
  if (slides.length > 0) {
    let currentSlide = 0;
    let slideInterval = null;

    function showSlide(index) {
      slides.forEach((s, i) => {
        s.classList.toggle("active", i === index);
      });
      if (bgSlides.length > 0) {
        bgSlides.forEach((bgs, i) => {
          bgs.classList.toggle("active", i === index);
        });
      }
      dots.forEach((d, i) => {
        d.classList.toggle("active", i === index);
      });
      currentSlide = index;
    }

    function nextSlide() {
      const nextIndex = (currentSlide + 1) % slides.length;
      showSlide(nextIndex);
    }

    function startSlideShow() {
      stopSlideShow();
      slideInterval = setInterval(nextSlide, 5500);
    }

    function stopSlideShow() {
      if (slideInterval) clearInterval(slideInterval);
    }

    dots.forEach((dot, idx) => {
      dot.addEventListener("click", () => {
        showSlide(idx);
        startSlideShow();
      });
    });

    const heroSection = document.querySelector(".home-hero");
    if (heroSection) {
      heroSection.addEventListener("mouseenter", stopSlideShow);
      heroSection.addEventListener("mouseleave", startSlideShow);
    }

    startSlideShow();
  }

  // Interactive Product Tabs with Automatic Rotation & Hover Pause
  const productTabBtns = document.querySelectorAll(".product-tab-btn");
  const productPanes = document.querySelectorAll(".product-pane");
  if (productTabBtns.length > 0) {
    let currentProdIndex = 0;
    let prodAutoInterval = null;

    function activateProductTab(index) {
      currentProdIndex = (index + productTabBtns.length) % productTabBtns.length;
      productTabBtns.forEach((b, i) => {
        b.classList.toggle("active", i === currentProdIndex);
      });
      productPanes.forEach((p) => p.classList.remove("active"));

      const targetId = productTabBtns[currentProdIndex].getAttribute("data-target");
      const targetPane = document.getElementById(targetId);
      if (targetPane) {
        targetPane.classList.add("active");
      }
    }

    function startProdAutoplay() {
      stopProdAutoplay();
      prodAutoInterval = setInterval(() => {
        activateProductTab(currentProdIndex + 1);
      }, 4500);
    }

    function stopProdAutoplay() {
      if (prodAutoInterval) {
        clearInterval(prodAutoInterval);
        prodAutoInterval = null;
      }
    }

    productTabBtns.forEach((btn, idx) => {
      btn.addEventListener("click", () => {
        activateProductTab(idx);
        startProdAutoplay();
      });
    });

    // Pause on hover over tabs and content area; resume on mouse leave
    const prodSection = document.querySelector(".products-section");
    if (prodSection) {
      prodSection.addEventListener("mouseenter", stopProdAutoplay);
      prodSection.addEventListener("mouseleave", startProdAutoplay);
    }

    startProdAutoplay();
  }

  // Technology Filter Tabs
  const techBtns = document.querySelectorAll(".tech-filter-btn");
  const techItems = document.querySelectorAll(".tech-item");
  if (techBtns.length > 0) {
    techBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        techBtns.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");

        const filter = btn.getAttribute("data-filter");
        techItems.forEach((item) => {
          if (filter === "all" || item.getAttribute("data-category") === filter) {
            item.style.display = "flex";
          } else {
            item.style.display = "none";
          }
        });
      });
    });
  }

  // Services Carousel
  const track = document.getElementById("servicesTrack");
  const prevBtn = document.getElementById("servicesPrev");
  const nextBtn = document.getElementById("servicesNext");
  if (track && prevBtn && nextBtn) {
    const cards = Array.from(track.querySelectorAll(".service-card"));
    let currentIndex = 0;
    let cardsPerView = 3;
    let carouselAutoplay = null;

    function getCardsPerView() {
      if (window.innerWidth <= 767) return 1;
      if (window.innerWidth <= 991) return 2;
      return 3;
    }

    function updateCarousel() {
      cardsPerView = getCardsPerView();
      const maxIndex = Math.max(0, cards.length - cardsPerView);
      currentIndex = Math.min(Math.max(0, currentIndex), maxIndex);

      if (cards.length > 0) {
        const cardWidth = cards[0].offsetWidth;
        const gap = parseInt(window.getComputedStyle(track).gap) || 20;
        const offset = currentIndex * (cardWidth + gap);
        track.style.transform = `translateX(-${offset}px)`;

        cards.forEach((c) => c.classList.remove("is-center"));
        if (cardsPerView === 3 && cards[currentIndex + 1]) {
          cards[currentIndex + 1].classList.add("is-center");
        } else if (cards[currentIndex]) {
          cards[currentIndex].classList.add("is-center");
        }

        prevBtn.disabled = currentIndex <= 0;
        nextBtn.disabled = currentIndex >= maxIndex;
      }
    }

    function scrollNext() {
      const maxIndex = Math.max(0, cards.length - cardsPerView);
      if (currentIndex >= maxIndex) {
        currentIndex = 0;
      } else {
        currentIndex++;
      }
      updateCarousel();
    }

    function scrollPrev() {
      const maxIndex = Math.max(0, cards.length - cardsPerView);
      if (currentIndex <= 0) {
        currentIndex = maxIndex;
      } else {
        currentIndex--;
      }
      updateCarousel();
    }

    function startCarouselTimer() {
      stopCarouselTimer();
      carouselAutoplay = setInterval(scrollNext, 4500);
    }

    function stopCarouselTimer() {
      if (carouselAutoplay) clearInterval(carouselAutoplay);
    }

    nextBtn.addEventListener("click", () => {
      scrollNext();
      startCarouselTimer();
    });

    prevBtn.addEventListener("click", () => {
      scrollPrev();
      startCarouselTimer();
    });

    const wrapper = track.closest(".services-carousel-wrapper");
    if (wrapper) {
      wrapper.addEventListener("mouseenter", stopCarouselTimer);
      wrapper.addEventListener("mouseleave", startCarouselTimer);
    }

    window.addEventListener("resize", updateCarousel, { passive: true });
    updateCarousel();
    startCarouselTimer();
  }

  // Testimonials Carousel / Slider Interaction
  const testTrack = document.getElementById("testimonialsTrack");
  const testPrevBtn = document.getElementById("testimonialsPrev");
  const testNextBtn = document.getElementById("testimonialsNext");
  const testDotsContainer = document.getElementById("testimonialsDots");

  if (testTrack) {
    const slides = Array.from(testTrack.querySelectorAll(".testimonial-slide"));
    let currentIndex = 0;
    let cardsPerView = 3;
    let autoplayTimer = null;

    function getCardsPerView() {
      if (window.innerWidth <= 767) return 1;
      if (window.innerWidth <= 991) return 2;
      return 3;
    }

    function renderDots() {
      if (!testDotsContainer) return;
      testDotsContainer.innerHTML = "";
      cardsPerView = getCardsPerView();
      const totalPages = Math.max(1, slides.length - cardsPerView + 1);

      for (let i = 0; i < totalPages; i++) {
        const dot = document.createElement("button");
        dot.type = "button";
        dot.className = `testimonial-carousel-dot ${i === currentIndex ? "active" : ""}`;
        dot.setAttribute("aria-label", `Go to slide page ${i + 1}`);
        dot.addEventListener("click", () => {
          currentIndex = i;
          updateCarousel();
          startAutoplay();
        });
        testDotsContainer.appendChild(dot);
      }
    }

    function updateCarousel() {
      cardsPerView = getCardsPerView();
      const maxIndex = Math.max(0, slides.length - cardsPerView);
      currentIndex = Math.min(Math.max(0, currentIndex), maxIndex);

      if (slides.length > 0) {
        const slideWidth = slides[0].offsetWidth;
        const gap = parseInt(window.getComputedStyle(testTrack).gap) || 24;
        const offset = currentIndex * (slideWidth + gap);
        testTrack.style.transform = `translateX(-${offset}px)`;

        if (testPrevBtn) testPrevBtn.disabled = currentIndex <= 0;
        if (testNextBtn) testNextBtn.disabled = currentIndex >= maxIndex;

        if (testDotsContainer) {
          const dots = testDotsContainer.querySelectorAll(".testimonial-carousel-dot");
          dots.forEach((dot, idx) => {
            dot.classList.toggle("active", idx === currentIndex);
          });
        }
      }
    }

    function scrollNext() {
      const maxIndex = Math.max(0, slides.length - cardsPerView);
      if (currentIndex >= maxIndex) {
        currentIndex = 0;
      } else {
        currentIndex++;
      }
      updateCarousel();
    }

    function scrollPrev() {
      const maxIndex = Math.max(0, slides.length - cardsPerView);
      if (currentIndex <= 0) {
        currentIndex = maxIndex;
      } else {
        currentIndex--;
      }
      updateCarousel();
    }

    function startAutoplay() {
      stopAutoplay();
      autoplayTimer = setInterval(scrollNext, 4500);
    }

    function stopAutoplay() {
      if (autoplayTimer) clearInterval(autoplayTimer);
    }

    if (testNextBtn) {
      testNextBtn.addEventListener("click", () => {
        scrollNext();
        startAutoplay();
      });
    }

    if (testPrevBtn) {
      testPrevBtn.addEventListener("click", () => {
        scrollPrev();
        startAutoplay();
      });
    }

    const testSection = testTrack.closest(".testimonials-section") || testTrack.closest(".testimonials-carousel-wrapper");
    if (testSection) {
      testSection.addEventListener("mouseenter", stopAutoplay);
      testSection.addEventListener("mouseleave", startAutoplay);
    }

    window.addEventListener("resize", () => {
      renderDots();
      updateCarousel();
    }, { passive: true });

    renderDots();
    updateCarousel();
    startAutoplay();
  }

  // Consultation & Contact Form Client-side Handling
  const contactForm = document.getElementById("contactForm");
  if (contactForm) {
    contactForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const feedback = document.getElementById("formMessage");
      const submitBtn = contactForm.querySelector('button[type="submit"]');
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : "";

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Sending... <span class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>';
      }

      if (feedback) {
        feedback.innerHTML = '<div class="text-info mt-2" style="font-size: 13px;"><i class="bi bi-hourglass-split me-1"></i> Submitting your request...</div>';
      }

      const formData = new FormData(contactForm);
      formData.append("form_source", "Contact & Consultation Page");

      try {
        const response = await fetch("submit_form.php", {
          method: "POST",
          body: formData,
        });

        const data = await response.json();

        if (response.ok && data.success) {
          if (feedback) {
            feedback.innerHTML = `
              <div class="alert alert-success mt-3" style="background: rgba(11, 224, 223, 0.15); border: 1px solid var(--accent-cyan); color: #fff; border-radius: 8px;">
                <i class="bi bi-check-circle-fill text-info me-2"></i> ${data.message || 'Thank you! Your request has been recorded. Our team will contact you within 24 hours.'}
              </div>
            `;
          }
          contactForm.reset();
        } else {
          if (feedback) {
            feedback.innerHTML = `
              <div class="alert alert-danger mt-3" style="background: rgba(255, 75, 75, 0.15); border: 1px solid #ff4b4b; color: #ffa3a3; border-radius: 8px;">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> ${data.message || 'Unable to submit your request. Please try again.'}
              </div>
            `;
          }
        }
      } catch (err) {
        if (feedback) {
          feedback.innerHTML = `
            <div class="alert alert-danger mt-3" style="background: rgba(255, 75, 75, 0.15); border: 1px solid #ff4b4b; color: #ffa3a3; border-radius: 8px;">
              <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Network error. Please try again later or email us at info@etechedge.com.
            </div>
          `;
        }
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
      }
    });
  }

  document.querySelectorAll(".quick-form").forEach((form) => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : "";
      const feedback = form.querySelector(".quick-form-message");

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Sending... <span class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>';
      }

      if (feedback) {
        feedback.innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split me-1"></i> Submitting...</span>';
      }

      const formData = new FormData(form);
      formData.append("form_source", "Home Page Quick Consultation");

      try {
        const response = await fetch("submit_form.php", {
          method: "POST",
          body: formData,
        });

        const data = await response.json();

        if (response.ok && data.success) {
          if (feedback) {
            feedback.innerHTML = `
              <div class="alert alert-success mt-2 p-2" style="background: rgba(11, 224, 223, 0.15); border: 1px solid var(--accent-cyan); color: #fff; border-radius: 6px;">
                <i class="bi bi-check-circle-fill text-info me-1"></i> ${data.message || 'Consultation request received! We will reach out within 24 hours.'}
              </div>
            `;
          } else {
            alert(data.message || "Thank you! Your project consultation request has been received. Our solutions team will reach out within 24 hours.");
          }
          form.reset();
        } else {
          if (feedback) {
            feedback.innerHTML = `
              <div class="alert alert-danger mt-2 p-2" style="background: rgba(255, 75, 75, 0.15); border: 1px solid #ff4b4b; color: #ffa3a3; border-radius: 6px;">
                <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> ${data.message || 'Unable to submit request. Please try again.'}
              </div>
            `;
          } else {
            alert(data.message || "Unable to submit your request. Please try again.");
          }
        }
      } catch (err) {
        if (feedback) {
          feedback.innerHTML = `
            <div class="alert alert-danger mt-2 p-2" style="background: rgba(255, 75, 75, 0.15); border: 1px solid #ff4b4b; color: #ffa3a3; border-radius: 6px;">
              <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Network error. Please try again later.
            </div>
          `;
        } else {
          alert("Network error. Please try again later or email us at info@etechedge.com.");
        }
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
      }
    });
  });

  // Blog Category Filter
  const blogFilterBtns = document.querySelectorAll(".blog-filters button");
  const blogCards = document.querySelectorAll(".post-card, .blog-card-modern");
  if (blogFilterBtns.length > 0) {
    blogFilterBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        blogFilterBtns.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        const category = btn.textContent.trim().toUpperCase();

        blogCards.forEach((card) => {
          const cardCat = card.getAttribute("data-category")?.toUpperCase() || "";
          if (category === "ALL" || cardCat.includes(category)) {
            card.parentElement.style.display = "block";
          } else {
            card.parentElement.style.display = "none";
          }
        });
      });
    });
  }

  // FAQ Accordion Interaction
  const faqItems = document.querySelectorAll(".faq-item");
  if (faqItems.length > 0) {
    faqItems.forEach((item) => {
      const questionBtn = item.querySelector(".faq-question");
      if (questionBtn) {
        questionBtn.addEventListener("click", () => {
          const isActive = item.classList.contains("active");
          // Close all other items
          faqItems.forEach((other) => {
            if (other !== item) other.classList.remove("active");
          });
          // Toggle current
          item.classList.toggle("active", !isActive);
        });
      }
    });
  }

  // Theme Switcher System (Floating bottom-left single toggle)
  // Default is Light Mode ('mverve'); Dark Mode is optional/chooseable.
  function initThemeSwitcher() {
    const savedTheme = localStorage.getItem("etechedge_theme") || "mverve";
    applyTheme(savedTheme);

    const toggleBtns = document.querySelectorAll(".theme-single-toggle-btn");
    toggleBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        const currentTheme = document.documentElement.getAttribute("data-theme") === "mverve" ? "mverve" : "dark";
        const nextTheme = currentTheme === "mverve" ? "dark" : "mverve";
        applyTheme(nextTheme);
      });
    });
  }

  function applyTheme(theme) {
    if (theme === "dark") {
      document.documentElement.removeAttribute("data-theme");
      localStorage.setItem("etechedge_theme", "dark");
    } else {
      document.documentElement.setAttribute("data-theme", "mverve");
      localStorage.setItem("etechedge_theme", "mverve");
    }

    // Update floating toggle buttons:
    // When Light Mode is active -> button prompts to switch to "Dark"
    // When Dark Mode is active -> button prompts to switch to "Light"
    document.querySelectorAll(".theme-single-toggle-btn").forEach((btn) => {
      const label = btn.querySelector(".theme-label-text");
      const icon = btn.querySelector(".theme-icon-wrap i");
      if (theme === "dark") {
        if (label) label.textContent = "Light";
        if (icon) icon.className = "bi bi-brightness-high";
        btn.setAttribute("title", "Switch to Light Mode");
        btn.setAttribute("aria-label", "Switch to Light Mode");
      } else {
        if (label) label.textContent = "Dark";
        if (icon) icon.className = "bi bi-moon-stars";
        btn.setAttribute("title", "Switch to Dark Mode");
        btn.setAttribute("aria-label", "Switch to Dark Mode");
      }
    });
  }

  initThemeSwitcher();
});

