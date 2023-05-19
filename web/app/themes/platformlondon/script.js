// Make whole category card a link (can't do this in block editor)
document.querySelectorAll(".platform-category-cards .wp-block-column").forEach(col => {
    const link = col.querySelector("h2 a")
    if (!link) {
        return
    }
    const url = link.getAttribute("href")
    const newLink = document.createElement("a")
    newLink.setAttribute("href", url)
    newLink.style.display = "block"
    newLink.style.height = "100%"
    newLink.style.padding = "1rem"
    const children = [...col.children]
    for(const child of children) {
        newLink.appendChild(child)
    }
    col.appendChild(newLink)
    col.style.padding = "0"
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

// Display content (hidden by pre-script.js)
document.body.style.visibility = "visible"
