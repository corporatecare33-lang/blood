// Site-wide motion, declared in Blade with data attributes:
//   data-hero                 home hero; optional parts: [data-hero-media], [data-hero-line], [data-hero-panel], [data-glyph]
//   data-intro                page-load entrance (page headers, auth card, admin title)
//   data-reveal               fades up when scrolled into view
//   data-reveal-stagger       reveals its direct children one after another
//   data-draw                 grows from the left when scrolled into view (rules, bars)
//   data-count-to="123"       counts up to the server-rendered number
// GSAP is code-split and only fetched when a page uses one of these.
// With prefers-reduced-motion, everything becomes a short opacity fade: no movement, no counting.

const SELECTOR = '[data-hero],[data-intro],[data-reveal],[data-reveal-stagger],[data-draw],[data-count-to]'
const root = document.documentElement

let gsapPromise = null
let ctx = null
let animatedBody = null

const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches

function loadGsap() {
    gsapPromise ??= Promise.all([import('gsap'), import('gsap/ScrollTrigger')]).then(([{ gsap }, { ScrollTrigger }]) => {
        gsap.registerPlugin(ScrollTrigger)
        return gsap
    })
    return gsapPromise
}

function animatePage(gsap) {
    let reduce = reduceMotion()
    let lift = (px) => (reduce ? 0 : px)
    let dur = (s) => (reduce ? 0.35 : s)
    let ease = 'power3.out'
    let onView = (trigger, start = 'top 88%') => ({ trigger, start, once: true })

    let hero = document.querySelector('[data-hero]')
    if (hero) {
        let tl = gsap.timeline({ defaults: { ease, duration: dur(0.8) } })
        // Every hero part is optional; skip tweens whose targets aren't in this page's markup.
        let step = (selector, from, to, at) => {
            let els = hero.querySelectorAll(selector)
            if (els.length) tl.fromTo(els, from, to, at)
        }
        step('[data-hero-media]', { autoAlpha: 0 }, { autoAlpha: 1, duration: dur(1.2), ease: 'power2.out' }, 0)
        if (!reduce) step('[data-hero-media] img', { scale: 1.12 }, { scale: 1, duration: 1.8, ease: 'power2.out' }, 0)
        step('[data-hero-line]', { autoAlpha: 0, y: lift(28) }, { autoAlpha: 1, y: 0, stagger: 0.09 }, 0.1)
        step('[data-hero-panel]', { autoAlpha: 0, y: lift(40) }, { autoAlpha: 1, y: 0, duration: dur(0.9) }, 0.3)
        step('[data-glyph]',
            { autoAlpha: 0, scale: reduce ? 1 : 0.6 },
            { autoAlpha: 1, scale: 1, duration: dur(0.5), stagger: 0.05, ease: reduce ? ease : 'back.out(2.2)' }, '-=0.45')
    }

    let intro = gsap.utils.toArray('[data-intro]')
    if (intro.length) {
        gsap.fromTo(intro, { autoAlpha: 0, y: lift(20) }, { autoAlpha: 1, y: 0, duration: dur(0.7), ease, stagger: 0.08, delay: 0.05 })
    }

    gsap.utils.toArray('[data-reveal]').forEach((el) => {
        gsap.fromTo(el, { autoAlpha: 0, y: lift(32) }, { autoAlpha: 1, y: 0, duration: dur(0.8), ease, scrollTrigger: onView(el) })
    })

    gsap.utils.toArray('[data-reveal-stagger]').forEach((group) => {
        if (!group.children.length) return
        gsap.fromTo(group.children, { autoAlpha: 0, y: lift(28) },
            { autoAlpha: 1, y: 0, duration: dur(0.7), ease, stagger: 0.1, scrollTrigger: onView(group, 'top 85%') })
    })

    gsap.utils.toArray('[data-draw]').forEach((el) => {
        gsap.fromTo(el, { autoAlpha: 0, scaleX: reduce ? 1 : 0, transformOrigin: 'left center' },
            { autoAlpha: 1, scaleX: 1, duration: dur(1), ease: 'power2.inOut', delay: 0.15, scrollTrigger: onView(el, 'top 92%') })
    })

    // Counting rewrites text, which revert() can't undo, so restore it ourselves.
    let counters = reduce ? [] : gsap.utils.toArray('[data-count-to]')
    let originals = counters.map((el) => el.textContent)
    counters.forEach((el) => {
        let end = Number(el.dataset.countTo)
        if (!end) return
        let n = { value: 0 }
        // Match Bn::num (Western grouping, Bangla digits); 'bn-BD' would regroup as ১২,৩৪,৫৬৭.
        let format = (v) => Math.round(v).toLocaleString('en-US').replace(/\d/g, (d) => '০১২৩৪৫৬৭৮৯'[d])
        el.textContent = format(0)
        gsap.to(n, {
            value: end, duration: 1.6, ease: 'power2.out', delay: 0.3, scrollTrigger: onView(el, 'top 95%'),
            onUpdate: () => (el.textContent = format(n.value)),
        })
    })

    return () => counters.forEach((el, i) => (el.textContent = originals[i]))
}

async function init() {
    // Livewire fires livewire:navigated on first load too; animate each page body once.
    if (animatedBody === document.body) return
    animatedBody = document.body

    if (!document.querySelector(SELECTOR)) return root.classList.add('motion-ready')

    try {
        let gsap = await loadGsap()
        ctx?.revert()
        ctx = gsap.context(() => animatePage(gsap))
    } finally {
        root.classList.add('motion-ready')
    }
}

// Small press feedback when a blood group is picked in the hero finder.
document.addEventListener('change', (e) => {
    let glyph = e.target.closest?.('[data-glyph]')?.querySelector('.group-glyph')
    if (!glyph || !gsapPromise || reduceMotion()) return
    gsapPromise.then((gsap) => gsap.fromTo(glyph, { scale: 0.86 }, { scale: 1, duration: 0.45, ease: 'back.out(3)', clearProps: 'transform' }))
})

// Revert before Livewire snapshots the page for the back button, so the cached HTML is clean.
// Re-hide on swap so the new page doesn't flash before its start state is set.
document.addEventListener('livewire:navigating', (e) => {
    ctx?.revert()
    ctx = null
    e.detail?.onSwap?.(() => {
        root.classList.remove('motion-ready')
        setTimeout(() => root.classList.add('motion-ready'), 3000)
    })
})
document.addEventListener('livewire:navigated', init)

init()
