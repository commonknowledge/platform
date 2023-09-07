try {
    // Make whole project card a link (can't do this in block editor)
    document.querySelectorAll(".platform-category-cards .wp-block-column").forEach(col => {
        const link = col.querySelector("h2 a")
        const header = col.querySelector("h2")
        if (!link) {
            return
        }
        const url = link.getAttribute("href")
        const newLink = document.createElement("a")
        newLink.setAttribute("href", url)
        newLink.style.display = "block"
        newLink.style.height = "100%"
        newLink.style.padding = "1rem"
        newLink.style.textDecoration = "none"
        header.style.textDecoration = "underline"
        const children = [...col.children]
        for (const child of children) {
            newLink.appendChild(child)
        }
        col.appendChild(newLink)
        col.style.padding = "0"
    })

    // Make post cards image and title glow at the same time
    const LINK_SELECTOR = '.wp-block-post-featured-image a,.wp-block-post-title a'
    document.querySelectorAll(".wp-block-post:not(.type-pl_project)").forEach(post => {
        post.querySelectorAll(LINK_SELECTOR).forEach(link => {
            const allLinks = link.closest('.wp-block-post').querySelectorAll('.wp-block-post-title a')
            link.addEventListener("mouseenter", () => {
                allLinks.forEach(otherLink => {
                    otherLink.classList.add("yellow-drop-shadow")
                })
            })
            link.addEventListener("mouseleave", () => {
                allLinks.forEach(otherLink => {
                    otherLink.classList.remove("yellow-drop-shadow")
                })
            })
        })
    })

    // Set up About page tabs
    const aboutContent = document.querySelector(".platform-about")
    if (aboutContent) {
        const aboutNav = document.createElement("nav")
        const aboutTabs = document.createElement("ul")
        aboutNav.appendChild(aboutTabs)
        aboutNav.setAttribute("class", "platform-tabs")
        const pageContent = aboutContent.parentElement
        const hr = pageContent.querySelector("hr")
        pageContent.insertBefore(aboutNav, hr)
        pageContent.removeChild(hr)

        // Break page content up into sections separated by H1 or H2 elements
        const sectionTitles = []
        const sections = {}
        let section = []
        let sectionTitle = "About" // Default if content doesn't start with a heading
        document.querySelectorAll(".platform-about > *").forEach(contentElement => {
            if (contentElement.tagName === "H1" || contentElement.tagName === "H2") {
                if (section.length) {
                    sectionTitles.push(sectionTitle)
                    sections[sectionTitle] = section
                }
                sectionTitle = contentElement.textContent
                section = []
            } else {
                section.push(contentElement)
            }
            aboutContent.removeChild(contentElement)
        })
        if (section.length) {
            sectionTitles.push(sectionTitle)
            sections[sectionTitle] = section
        }

        // Add buttons at end of content, populated in displayTab()
        let finalButtons = null

        const displayTab = (titleToShow) => {
            for (const sectionTitle of sectionTitles) {
                const tabContent = document.querySelector(`.platform-about [id="${sectionTitle}"]`)
                const tab = document.querySelector(`[id="${sectionTitle}-button"]`)
                if (sectionTitle === titleToShow) {
                    tabContent.style.display = "block"
                    tab.setAttribute("class", "active")
                } else {
                    tabContent.style.display = "none"
                    tab.setAttribute("class", "")
                }
            }

            const sectionIndex = sectionTitles.findIndex(t => t === titleToShow)
            const next = sectionIndex === sectionTitles.length - 1 ? null : sectionTitles[sectionIndex + 1]
            const prev = sectionIndex === 0 ? null : sectionTitles[sectionIndex - 1]

            if (finalButtons) {
                aboutContent.removeChild(finalButtons)
            }
            finalButtons = document.createElement("div")
            finalButtons.setAttribute("class", "platform-about-final-buttons")

            if (prev) {
                const prevButton = document.createElement("button")
                prevButton.textContent = `← Previous: ${prev}`
                prevButton.style.marginRight = "auto"
                prevButton.setAttribute("type", "button")
                prevButton.setAttribute("class", "btn-default")
                prevButton.addEventListener("click", () => {
                    window.scrollTo(0, 0)
                    displayTab(prev)
                })
                finalButtons.appendChild(prevButton)
            }

            if (next) {
                const nextButton = document.createElement("button")
                nextButton.textContent = `Next: ${next} →`
                nextButton.style.marginLeft = "auto"
                nextButton.style.zIndex = "1000"
                nextButton.setAttribute("type", "button")
                nextButton.setAttribute("class", "btn-default")
                nextButton.addEventListener("click", () => {
                    window.scrollTo(0, 0)
                    displayTab(next)
                })
                finalButtons.appendChild(nextButton)
            }

            aboutContent.appendChild(finalButtons)
        }

        // Create tab for each section
        for (const sectionTitle of sectionTitles) {
            const id = sectionTitle
            const tab = document.createElement("li")
            const button = document.createElement("button")
            button.setAttribute("type", "button")
            button.setAttribute("id", `${sectionTitle}-button`)
            button.textContent = sectionTitle
            button.addEventListener("click", () => {
                displayTab(id)
            })
            tab.appendChild(button)
            aboutTabs.appendChild(tab)

            const tabContent = document.createElement("div")
            tabContent.setAttribute("id", id)
            for (const element of sections[sectionTitle]) {
                tabContent.appendChild(element)
            }
            aboutContent.appendChild(tabContent)
        }

        // Move the classes from the main container to the tab containers
        // This is because the styles should apply to each tab container instead
        const aboutClasses = aboutContent.getAttribute("class").replace("platform-about", "")
        aboutContent.setAttribute("class", "platform-about")
        for (const tabContent of aboutContent.children) {
            tabContent.setAttribute("class", aboutClasses)
        }

        displayTab(sectionTitles[0])
    }

    /* Set up projects carousel */
    document.querySelectorAll(".projects-carousel").forEach((carousel) => {
        let currentIndex = 1

        const cardContainer = carousel.querySelector(".wp-block-post-template")
        const cards = carousel.querySelectorAll(".wp-block-post")
        const buttons = carousel.querySelectorAll(".projects-carousel__nav button")

        const displayCard = (index) => {
            if (index < 0) {
                index = 0
            }
            if (index >= cards.length) {
                index = cards.length - 1
            }

            const cardWidth = cards[0].clientWidth
            cards.forEach((card, i) => {
                card.style.transform = i === index ? "scale(1.0)" : "scale(0.8)"
            })

            const transformX = `calc(-${cardWidth}px * ${index}.5)`
            cardContainer.style.transform = `translate3d(${transformX}, 0, 0)`
            currentIndex = index
        }

        cards.forEach((card, i) => {
            card.addEventListener("click", () => displayCard(i))
        })

        buttons[0].addEventListener("click", () => displayCard(currentIndex - 1))
        buttons[1].addEventListener("click", () => displayCard(currentIndex + 1))

        // Disable 
        if (cards.length === 1) {
            const transition = getComputedStyle(cardContainer).transition
            cardContainer.style.transition = "none"
            cards[0].style.transition = "none"
            displayCard(0)
        }
    })

    /* Set up platform stack movement */
    document.querySelectorAll(".platform-stack").forEach((stack) => {
        let currentIndex = 0

        const cardContainer = stack.querySelector(".wp-block-post-template")
        const cards = stack.querySelectorAll(".wp-block-post")
        const buttons = stack.querySelectorAll(".platform-stack__nav button")
        const leftButton = buttons[0]
        const rightButton = buttons[buttons.length - 1]

        const moveLeft = () => {
            if (currentIndex <= 0) {
                return
            }

            currentIndex -= 1
            updateStack()
        }

        const moveRight = () => {
            currentIndex += 1
            updateStack()
        }

        /**
         * Detect if the right button should be hidden for a given
         * container offset. This is done by calculating where this
         * offset would put the right edge of the last card. If it
         * is within the window, the button should be hidden.
         */
        const shouldHideRightButton = (containerOffsetX) => {
            const firstCard = cards[0]
            const lastCard = cards[cards.length - 1]
            const firstCardLeft = firstCard.getBoundingClientRect().left
            const lastCardRight = lastCard.getBoundingClientRect().right
            const stackWidth = lastCardRight - firstCardLeft
            return (stackWidth - containerOffsetX) < window.innerWidth
        }

        const updateStack = () => {
            // Move the container left to display the card at currentIndex.
            // This is done by finding the total width of cards before the current
            // card, and moving the container left by this amount.
            let offsetX = 0
            for (let i = 0; i < currentIndex; i++) {
                const card = cards[i]
                const dimensions = card.getBoundingClientRect()
                offsetX += dimensions.width
            }

            cardContainer.style.transform = `translateX(-${offsetX}px)`

            if (currentIndex === 0) {
                leftButton.style.visibility = "hidden"
            } else {
                leftButton.style.visibility = null
            }

            if (shouldHideRightButton(offsetX)) {
                rightButton.style.visibility = "hidden"
            } else {
                rightButton.style.visibility = null
            }
        }

        updateStack()

        leftButton.addEventListener("click", moveLeft)
        rightButton.addEventListener("click", moveRight)
    })

    /* Bring illustration element to the top on mouseenter (can't be done in CSS) */
    document.querySelectorAll('.platform-illustration svg').forEach(svg => {
        // Get the bounds of the SVG content
        const bbox = svg.getBBox();
        svg.setAttribute("viewBox", `${bbox.x} ${bbox.y} ${bbox.width} ${bbox.height}`);
    })

    const CATEGORIES = [
        "community",
        "economy",
        "liberation",
        "culture",
        "energy",
    ]

    const findCategory = (mouseX, mouseY) => {
        for (const category of CATEGORIES) {
            const svg = document.querySelector(`svg[id=${category}-svg]`)

            let point = svg.createSVGPoint()
            point.x = mouseX
            point.y = mouseY
            point = point.matrixTransform(svg.getScreenCTM().inverse())

            const paths = document.querySelectorAll(`g[id=${category}-base] path`)
            for (const path of paths) {
                if (path.isPointInFill(point) || path.isPointInStroke(point)) {
                    return category
                }
            }
        }
        return null
    }

    let focusedCategory
    const illustration = document.querySelector('.platform-illustration')
    const handleIllustrationMouseMove = (e) => {
        const { clientX: mouseX, clientY: mouseY } = e
        let category = findCategory(mouseX, mouseY)

        if (category) {
            if (category !== focusedCategory) {
                focusedCategory = category
                displayCategorySvg(focusedCategory)
            }
        } else {
            // If mouse is outside all bounding boxes, hide all illustrations
            for (const category of CATEGORIES) {
                const svg = document.querySelector(`g[id=${category}-base]`)
                const bbox = svg.getBoundingClientRect()
                if (bbox.left <= mouseX && bbox.right >= mouseX && bbox.top <= mouseY && bbox.bottom >= mouseY) {
                    return
                }
            }
            focusedCategory = null
            displayCategorySvg(null)
        }
    }

    const displayCategorySvg = (category) => {
        document.querySelectorAll('.platform-illustration g').forEach(g => {
            if (g.id.startsWith(category)) {
                g.style.opacity = 1
            } else if (!g.id.endsWith("-base")) {
                g.style.opacity = 0
            } else {
                g.style.opacity = category ? 0.1 : 1
            }
        })
        document.querySelectorAll('.platform-illustration svg').forEach(svg => {
            if (svg.id.startsWith(category)) {
                svg.style.zIndex = 99
                svg.style.transform = `translateY(-50px)`
            } else {
                svg.style.zIndex = 10
                svg.style.transform = `translateY(0)`
            }
        })
    }
    window.dc = displayCategorySvg
    illustration?.addEventListener("mousemove", handleIllustrationMouseMove)
    illustration?.addEventListener("mouseleave", () => {
        displayCategorySvg(null)
    })

    /* Do search when user clicks search icon */
    document.querySelectorAll(".wp-block-search").forEach(search => {
        search.addEventListener("click", (e) => {
            const { clientX: mouseX } = e
            const { right } = search.getBoundingClientRect()
            // Detect if click is in the rightmost 50 pixels of the input
            // Horrible hack because pseudo-element clicks are not detected in JS
            if (mouseX < right && mouseX > right - 50) {
                search.submit()
            }
        })
    })

    /* Set up search sort select */
    document.querySelectorAll('.search-sort select').forEach(select => {
        select.addEventListener("change", () => {
            const value = select.value
            const path = location.pathname
            const queryParams = new URLSearchParams(location.search)
            queryParams.set("sort", value)
            location.href = `${path}?${queryParams}`
        })
    })

   /* Set up filers accordion */

    const filters = document.querySelector('.search-filter');
    
    const closeSearchFilterSection = (section) => {
    const button = section.querySelector(".search-filter__expand");
    const optionsList = section.querySelector(".search-filter__options");
    const closed = button.getAttribute("data-closed");
    
    if (closed) {
        button.removeAttribute("data-closed");
        optionsList.style.height = optionsList.getAttribute("data-original-height");
    } else {
        button.setAttribute("data-closed", true);
        optionsList.style.height = 0;
    }
    };


    document.querySelectorAll('.search-filter__section').forEach(section => {
    const button = section.querySelector(".search-filter__expand");
    const optionsList = section.querySelector(".search-filter__options");

    const initialOptionsListHeight = optionsList.clientHeight;

    button.addEventListener("click", () => {
        closeSearchFilterSection(section);
    });


    optionsList.style.height = initialOptionsListHeight + "px";
    optionsList.setAttribute("data-original-height", optionsList.style.height);


    closeSearchFilterSection(section);
    });


   /* Toggle filters display on mobile  */

    const filterButton = document.querySelector('.filters-toggle');

    function hideFilters() {
    filters.style.display = "none";
    filterButton.classList.remove('filters-open');
    }

    function adjustFiltersDisplay() {
    try {
        if (filters) {
        if (window.innerWidth > 767) {
            filters.style.display = "block";
            filterButton.style.display = "none";
            filterButton.classList.remove('filters-open');
        } else {
            filterButton.style.display = "block";
            filterButton.classList.add('filters-open');
            hideFilters();
        }
        }
    } catch (error) {

    }
    }

    if (filterButton) {
    filterButton.addEventListener('click', function () {
        if (filters.style.display === "block") {
        filters.style.display = "none";
        filterButton.classList.remove('filters-open');
        } else {
        filters.style.display = "block";
        filterButton.classList.add('filters-open');
        }
    });
    }

    adjustFiltersDisplay();
    window.addEventListener('resize', adjustFiltersDisplay);


    //  Show number of selected filters next to filter section heading
    function updateCheckboxCount(sectionId) {
        const checkboxes = document.querySelectorAll(`#${sectionId} input[type="checkbox"]`);
        let selectedCount = 0;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedCount++;
            }
        });

        const countElements = document.querySelectorAll(`#${sectionId} #count`);
        
        countElements.forEach(countElement => {
            countElement.textContent = ` (${selectedCount})`;
        });
    }

    const sections = document.querySelectorAll('.search-filter__section');
    sections.forEach((section, index) => {
        const sectionId = `section${index + 1}`;
        section.setAttribute('id', sectionId);

        const checkboxes = section.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => updateCheckboxCount(sectionId));
        });

        updateCheckboxCount(sectionId);
    });

    
    /* Set up search filter checkboxes */
    const EXCLUSIVE_PARAMS = ["pl_post_type", "pl_resource_type", "pl_project_type"]
    document.querySelectorAll('.search-filter input[type=checkbox]').forEach(checkbox => {
        checkbox.addEventListener("change", () => {
            const checked = checkbox.checked
            const value = checkbox.value
            const param = checkbox.getAttribute("data-param")

            const path = location.pathname
            const queryParams = new URLSearchParams(location.search)

            if (EXCLUSIVE_PARAMS.includes(param)) {
                for (const exclusiveParam of EXCLUSIVE_PARAMS) {
                    queryParams.delete(exclusiveParam)
                    if (exclusiveParam !== param) {
                        document
                            .querySelectorAll(`[id^='filter-${exclusiveParam}']`)
                            .forEach(checkbox => {
                                checkbox.checked = false
                            })
                    }
                }
            }

            const currentValue = queryParams.get(param) || ''
            let currentValues = currentValue.split(",").filter(Boolean)
            if (checked) {
                currentValues.push(value)
            } else {
                currentValues = currentValues.filter(v => v !== value)
            }
            if (currentValues.length) {
                queryParams.set(param, currentValues.join(","))
            } else {
                queryParams.delete(param)
            }
            location.href = `${path}?${queryParams}`
        })
    })

    /* Set up search filter accordion */




    /* Set up timeline */
    const timelineEntriesContainer = document.querySelector(".platform-timeline__entries")
    const timelineEntries = document.querySelectorAll(".platform-timeline__entry")

    if (timelineEntries.length) {
        const decadeLinks = document.querySelector(".platform-timeline-links__list")
        const decadeLinksPosition = decadeLinks.getBoundingClientRect().bottom

        decadeLinks.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", (e) => {
                e.preventDefault()
                const decade = link.getAttribute("href").split("#")[1]
                const target = document.getElementById(decade)
                window.scrollTo({
                    left: 0,
                    top: target.offsetTop,
                    behavior: "smooth"
                })
            })
        });

        const yearMarkerContainer = document.querySelector(".platform-timeline__marker")
        const yearMarker = document.querySelector(".platform-timeline__year")
        const yearMarkerPosition = document.querySelector(
            ".platform-timeline__marker .platform-timeline__circle"
        ).getBoundingClientRect().top
        const activeLine = document.querySelector(".platform-timeline__active-line")

        const updateTimeline = () => {
            const currentDisplayedYear = yearMarker.textContent
            let activeEntryIndex = 0
            for (let i = 0; i < timelineEntries.length; i++) {
                const entry = timelineEntries[i]
                entry.removeAttribute("data-active")
                if (entry.getBoundingClientRect().top < yearMarkerPosition - 8) {
                    activeEntryIndex = i
                    entry.setAttribute("data-active", true)
                }
            }

            const activeEntry = timelineEntries[activeEntryIndex]
            const activeYear = activeEntry.getAttribute("data-year")
            if (activeYear !== currentDisplayedYear) {
                yearMarker.textContent = activeYear
            }

            const activeLineHeight = Math.min(
                yearMarkerPosition - timelineEntries[0].getBoundingClientRect().top,
                timelineEntriesContainer.clientHeight
            )
            activeLine.style.height = activeLineHeight + "px"

            // Stop line scrolling into the footer
            if (timelineEntriesContainer.getBoundingClientRect().bottom < yearMarkerPosition) {
                yearMarkerContainer.style.position = "absolute"
                yearMarkerContainer.style.top = timelineEntriesContainer.clientHeight - 8 + "px"
            } else {
                yearMarkerContainer.style.position = ""
                yearMarkerContainer.style.top = ""
            }

            // Stop decade links scrolling into the footer
            if (timelineEntriesContainer.getBoundingClientRect().bottom < decadeLinksPosition) {
                decadeLinks.style.position = "absolute"
                decadeLinks.style.top = timelineEntriesContainer.clientHeight - decadeLinks.clientHeight + "px"
            } else {
                decadeLinks.style.position = ""
                decadeLinks.style.top = ""
            }
        }

        window.addEventListener("scroll", updateTimeline)
        updateTimeline()
    }

    // Fit SVG viewbox to the inner content. Used in the Project Details Footer block to display just one of the
    // category SVGs
    document.querySelectorAll(".post-details-footer svg").forEach((svg) => {
        // Get the bounds of the SVG content
        const bbox = svg.getBBox();
        svg.setAttribute("viewBox", `${bbox.x} ${bbox.y} ${bbox.width} ${bbox.height}`);
    })

    /**
     * Invert site logo and move it inside navbar when mobile navbar is opened
     */
    const logo = document.querySelector("header .wp-block-site-logo img")
    const link = document.querySelector("header .wp-block-site-logo a")
    const originalParent = document.querySelector(".wp-block-site-logo")
    const newParentDiv = document.querySelector(".wp-block-navigation__responsive-dialog");
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.classList.contains('is-menu-open')) {
                /* Make an image monochrome with this one weird trick! */
                logo.style.filter = "brightness(0) invert(1)"
                newParentDiv.insertBefore(link, newParentDiv.children[1])
                link.appendChild(logo)

            } else {
                logo.style.filter = ""
                originalParent.appendChild(logo);
            }
        });
    });

    observer.observe(document.querySelector('.wp-block-navigation__responsive-container'), {
        attributes: true,
        attributeFilter: ['class']
    });

    /**
     * Set up download PDF dropdowns
     */
    document.querySelectorAll(".post-download-link").forEach((downloadSelect) => {
        downloadSelect.addEventListener("change", function () {
            if (downloadSelect.value) {
                window.open(downloadSelect.value, "_blank")
            }
            downloadSelect.value = ""
        })
    })

    /**
     * Hide empty related sections
     */
    document.querySelectorAll(".wp-block-heading + .wp-block-query").forEach((queryLoop) => {
        if (!queryLoop.children.length) {
            queryLoop.previousElementSibling.style.display = "none"
        }
    })
} catch (e) {
    console.error("Error", e)
}

/* Apply link to whole of category cards on About page */
const nestedLink = document.querySelector('.wp-block-heading.stretched-link a');
 
if (nestedLink) {
    const h2Element = nestedLink.parentElement;
    if (h2Element) {
    h2Element.innerHTML = h2Element.textContent;
    }
}


// Display content (hidden by pre-script.js)
document.body.style.visibility = "visible"
