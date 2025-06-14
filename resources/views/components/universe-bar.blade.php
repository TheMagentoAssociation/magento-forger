<style>
    #magento-opensource-universe-bar-wrapper {
        background-color: #3c3c3c;
    }

    #magento-opensource-universe-bar {
        width: 1320px;
        margin-left: auto;
        margin-right: auto;
    }

    #magento-opensource-universe-bar-mainnav {
        padding: 0;
        margin: 0;
    }

    #magento-opensource-universe-bar-mainnav li {
        padding: 0.4rem 1rem;
        margin: 0;
        display: inline-flex;
        align-items: center;
        border-right: 1px solid #888888;
        font-size: 0.9rem;
        position: relative;
    }

    #magento-opensource-universe-bar-mainnav li:last-child {
        border-right: none;
    }

    #magento-opensource-universe-bar-mainnav li a {
        text-decoration: none;
        color: #bbbbbb;
        display: flex;
        align-items: center;
        height: 100%;
    }
    #magento-opensource-universe-bar-mainnav li a svg {
        margin-top: -2px;
    }

    #magento-opensource-universe-bar-mainnav li ul {
        position: absolute;
        z-index: 100;
        top: 100%;
        left: 0;
        padding: 0;
        margin: 0;
        display: none;
    }

    #magento-opensource-universe-bar-mainnav li:hover ul {
        display: block;
    }

    #magento-opensource-universe-bar-mainnav li ul li {
        display: block;
        background-color: #3c3c3c;
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
        border: none;
    }

    .magento-opensource-universe-bar-intro {
        color: #dddddd;
    }

    /* Media Queries for Bootstrap Breakpoints */
    @media (max-width: 575.98px) {
        #magento-opensource-universe-bar {
            width: 100%;
            padding: 0 10px;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        #magento-opensource-universe-bar {
            width: 540px;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        #magento-opensource-universe-bar {
            width: 720px;
        }
    }

    @media (min-width: 992px) and (max-width: 1199.98px) {
        #magento-opensource-universe-bar {
            width: 960px;
        }
    }

    @media (min-width: 1200px) and (max-width: 1399.98px) {
        #magento-opensource-universe-bar {
            width: 1140px;
        }
    }

    @media (min-width: 1400px) {
        #magento-opensource-universe-bar {
            width: 1320px;
        }
    }
</style>

<div id="magento-opensource-universe-bar-wrapper">
    <nav id="magento-opensource-universe-bar">
        <ul id="magento-opensource-universe-bar-mainnav">
            <li class="magento-opensource-universe-bar-intro">Explore the Magento Open Source Universe</li>
            <li>
                <a href="https://magento-opensource.com" target="_blank">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Magento Opensource
                </a>
            </li>
            <li>
                <a href="https://magentoassociation.org" target="_blank">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Magento Association
                </a>
            </li>
            <li>
                <a href="https://meet-magento.com" target="_blank">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Meet Magento
                </a>
            </li>
            <li>
                <a href="#">
                    <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 3l4 4 4-4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                    </svg>
                    Development Resources
                </a>
                <ul class="magento-opensource-universe-bar-subnav">
                    <li>
                        <a href="https://github.com/magento/magento2" target="_blank">
                            <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                            </svg>
                            GitHub Repository
                        </a>
                    </li>
                    <li>
                        <a href="https://forger.magento-opensource.com" target="_blank">
                            <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                            </svg>
                            Magento Forger
                        </a>
                    </li>
                    <!--<li>
                        <a href="https://docs.magento-opensource.com" target="_blank">
                            <svg width="10" height="10" viewBox="0 0 10 10" style="margin-right: 0.5rem; vertical-align: middle;" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 1l4 4-4 4" stroke="#bbbbbb" stroke-width="2" fill="none"/>
                            </svg>
                            Documentation
                        </a>
                    </li>-->
                </ul>
            </li>
        </ul>
    </nav>
</div>
