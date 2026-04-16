<template>
    <div class="front-sidebar" v-if="sideBarStatus || !isMobile">
        <div class="front-sidebar_logo">
            <router-link to="/trophy-room" >
                <div>
                    <img src="../../../web/images/web/img/tr-logo.svg" alt="logo">
                </div>
            </router-link>
            <div>
                <img class="sidebar_component" v-if="isMobile" src="../../../web/images/web/img/icons/close.svg" alt="arrow-down" @click="closeSideBar">
            </div>
        </div>
        <ul class="sidebar_menu">
            <li>
                <router-link to="/trophy-room" :class="{ active_item: $route.path === '/trophy-room' }">
                    <svg v-if="$route.path === '/trophy-room'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                        <path d="M21.3555 1.58782H18.5323C18.5425 1.27514 18.5482 0.960566 18.5482 0.644531C18.5482 0.288535 18.2595 0 17.9037 0H4.0963C3.74047 0 3.45177 0.288535 3.45177 0.644531C3.45177 0.960566 3.45748 1.27514 3.46771 1.58782H0.644531C0.288535 1.58782 0 1.87636 0 2.23236C0 5.12033 0.754789 7.84497 2.12523 9.90464C3.47995 11.9408 5.28447 13.1086 7.23388 13.2223C7.67581 13.7032 8.14292 14.0953 8.6295 14.3944V17.259H7.54823C6.24104 17.259 5.17773 18.3225 5.17773 19.6295V20.7108H5.13193C4.77589 20.7108 4.4874 20.9995 4.4874 21.3553C4.4874 21.7113 4.77589 21.9998 5.13193 21.9998H16.8681C17.2241 21.9998 17.5126 21.7113 17.5126 21.3553C17.5126 20.9995 17.2241 20.7108 16.8681 20.7108H16.8223V19.6295C16.8223 18.3225 15.759 17.259 14.4518 17.259H13.3705V14.3944C13.8571 14.0954 14.3244 13.7032 14.7663 13.2223C16.7155 13.1086 18.52 11.9408 19.8749 9.90464C21.2453 7.84497 22 5.12033 22 2.23236C22 1.87636 21.7115 1.58782 21.3555 1.58782ZM3.19846 9.19063C2.06804 7.49182 1.40387 5.2699 1.30268 2.87689H3.53972C3.77218 5.81857 4.46269 8.53767 5.54198 10.696C5.71386 11.0398 5.89359 11.3645 6.08008 11.6704C5.00801 11.2655 4.01586 10.4192 3.19846 9.19063ZM15.5332 19.6295V20.7109H6.4668V19.6295C6.4668 19.0333 6.95191 18.5481 7.54823 18.5481H14.4518C15.0481 18.5481 15.5332 19.0333 15.5332 19.6295ZM12.0814 17.259H9.91856V14.9545C10.2729 15.0478 10.6339 15.0963 11 15.0963C11.3661 15.0963 11.7271 15.0478 12.0814 14.9545V17.259ZM12.4806 13.421C12.4515 13.4329 12.4237 13.4474 12.397 13.4633C11.9414 13.6899 11.4733 13.8072 11 13.8072C10.5268 13.8072 10.0589 13.6899 9.60352 13.4637C9.57646 13.4473 9.54824 13.4329 9.51908 13.4207C9.01368 13.1535 8.52479 12.7499 8.06236 12.2197C8.03818 12.1851 8.01069 12.153 7.98029 12.1237C7.52121 11.5787 7.08933 10.9083 6.69492 10.1195C5.5104 7.75062 4.82625 4.6359 4.74839 1.28906H17.2516C17.1736 4.6359 16.4894 7.75083 15.3051 10.1195C14.9107 10.9084 14.4788 11.5787 14.0199 12.1237C13.9893 12.1529 13.9617 12.1852 13.9375 12.2199C13.4751 12.7503 12.986 13.1536 12.4806 13.421ZM18.8015 9.19063C17.9841 10.4192 16.9919 11.2655 15.9199 11.6704C16.1128 11.3533 16.2924 11.0282 16.458 10.696C17.5373 8.53772 18.2276 5.81857 18.4603 2.87689H20.6974C20.5961 5.2699 19.932 7.49182 18.8015 9.19063Z" fill="currentColor"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                        <path d="M21.3555 1.58782H18.5323C18.5425 1.27514 18.5482 0.960566 18.5482 0.644531C18.5482 0.288535 18.2595 0 17.9037 0H4.0963C3.74047 0 3.45177 0.288535 3.45177 0.644531C3.45177 0.960566 3.45748 1.27514 3.46771 1.58782H0.644531C0.288535 1.58782 0 1.87636 0 2.23236C0 5.12033 0.754789 7.84497 2.12523 9.90464C3.47995 11.9408 5.28447 13.1086 7.23388 13.2223C7.67581 13.7032 8.14292 14.0953 8.6295 14.3944V17.259H7.54823C6.24104 17.259 5.17773 18.3225 5.17773 19.6295V20.7108H5.13193C4.77589 20.7108 4.4874 20.9995 4.4874 21.3553C4.4874 21.7113 4.77589 21.9998 5.13193 21.9998H16.8681C17.2241 21.9998 17.5126 21.7113 17.5126 21.3553C17.5126 20.9995 17.2241 20.7108 16.8681 20.7108H16.8223V19.6295C16.8223 18.3225 15.759 17.259 14.4518 17.259H13.3705V14.3944C13.8571 14.0954 14.3244 13.7032 14.7663 13.2223C16.7155 13.1086 18.52 11.9408 19.8749 9.90464C21.2453 7.84497 22 5.12033 22 2.23236C22 1.87636 21.7115 1.58782 21.3555 1.58782ZM3.19846 9.19063C2.06804 7.49182 1.40387 5.2699 1.30268 2.87689H3.53972C3.77218 5.81857 4.46269 8.53767 5.54198 10.696C5.71386 11.0398 5.89359 11.3645 6.08008 11.6704C5.00801 11.2655 4.01586 10.4192 3.19846 9.19063ZM15.5332 19.6295V20.7109H6.4668V19.6295C6.4668 19.0333 6.95191 18.5481 7.54823 18.5481H14.4518C15.0481 18.5481 15.5332 19.0333 15.5332 19.6295ZM12.0814 17.259H9.91856V14.9545C10.2729 15.0478 10.6339 15.0963 11 15.0963C11.3661 15.0963 11.7271 15.0478 12.0814 14.9545V17.259ZM12.4806 13.421C12.4515 13.4329 12.4237 13.4474 12.397 13.4633C11.9414 13.6899 11.4733 13.8072 11 13.8072C10.5268 13.8072 10.0589 13.6899 9.60352 13.4637C9.57646 13.4473 9.54824 13.4329 9.51908 13.4207C9.01368 13.1535 8.52479 12.7499 8.06236 12.2197C8.03818 12.1851 8.01069 12.153 7.98029 12.1237C7.52121 11.5787 7.08933 10.9083 6.69492 10.1195C5.5104 7.75062 4.82625 4.6359 4.74839 1.28906H17.2516C17.1736 4.6359 16.4894 7.75083 15.3051 10.1195C14.9107 10.9084 14.4788 11.5787 14.0199 12.1237C13.9893 12.1529 13.9617 12.1852 13.9375 12.2199C13.4751 12.7503 12.986 13.1536 12.4806 13.421ZM18.8015 9.19063C17.9841 10.4192 16.9919 11.2655 15.9199 11.6704C16.1128 11.3533 16.2924 11.0282 16.458 10.696C17.5373 8.53772 18.2276 5.81857 18.4603 2.87689H20.6974C20.5961 5.2699 19.932 7.49182 18.8015 9.19063Z" fill="currentColor"/>
                    </svg>
                    <span>
                        Trophy Room
                    </span>
                </router-link>
            </li>
            <li>
                <router-link to="/validate" :class="{ active_item: $route.path === '/validate' }">
                    <svg v-if="$route.path === '/validate'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C13.5689 2 15.0535 2.36132 16.375 3.00529M20.125 5.75L11.375 14.5L8.875 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C13.5689 2 15.0535 2.36132 16.375 3.00529M20.125 5.75L11.375 14.5L8.875 12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>
                        Validate
                    </span>
                </router-link>
            </li>
            <li>
                <router-link to="/forge" :class="{ active_item: $route.path === '/forge' }">
                    <svg v-if="$route.path === '/forge'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <g clip-path="url(#clip0_1957_7171)">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43783 7.87275H4.78074V9.61888C3.02385 9.6487 1.97392 8.72638 1.43783 7.87275ZM6.08074 10.2142V10.233V10.7709C6.71032 11.0554 7.4264 11.6016 7.68769 12.5016C7.91819 13.2955 7.75102 14.2067 7.11206 15.2228H16.2267C15.9418 14.7414 15.7238 14.1719 15.706 13.5669C15.6893 12.9972 15.8525 12.4122 16.2775 11.884C16.6939 11.3665 17.3308 10.941 18.2025 10.6141C20.6182 9.70826 21.8059 9.14269 22.2807 8.87507V7.14998H6.08074V7.22275V10.2142ZM17.8507 15.2228C17.4179 14.7752 17.0229 14.1213 17.0055 13.5287C16.9972 13.2484 17.0716 12.9708 17.2903 12.699C17.5176 12.4165 17.9307 12.1045 18.659 11.8314C21.4869 10.7709 22.7701 10.129 23.2 9.83556C23.4813 9.64351 23.5807 9.34202 23.5807 9.0985V6.79998C23.5807 6.2753 23.1554 5.84998 22.6307 5.84998H5.73074C5.28438 5.84998 4.90993 6.15781 4.80808 6.57275H0.695884C0.151987 6.57275 -0.316622 7.11431 -0.0935012 7.70385C0.393644 8.99102 1.89056 10.9646 4.78074 10.9199V10.9965C4.78074 11.4304 5.06628 11.7703 5.41632 11.9021C5.89027 12.0806 6.30904 12.4156 6.43924 12.864C6.55898 13.2765 6.50195 14.0283 5.50243 15.2228H4.43074C4.1546 15.2228 3.93074 15.4466 3.93074 15.7228V18.7228C3.93074 18.9989 4.1546 19.2228 4.43074 19.2228H18.4307C18.7069 19.2228 18.9307 18.9989 18.9307 18.7228V15.7228C18.9307 15.4466 18.7069 15.2228 18.4307 15.2228H17.8507ZM5.23074 17.9228V16.5228H17.6307V17.9228H5.23074Z" fill="currentColor"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_1957_7171">
                                <rect width="24" height="24" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <g clip-path="url(#clip0_1957_6332)">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43783 7.87275H4.78074V9.61888C3.02385 9.6487 1.97392 8.72638 1.43783 7.87275ZM6.08074 10.2142V10.233V10.7709C6.71032 11.0554 7.4264 11.6016 7.68769 12.5016C7.91819 13.2955 7.75102 14.2067 7.11206 15.2228H16.2267C15.9418 14.7414 15.7238 14.1719 15.706 13.5669C15.6893 12.9972 15.8525 12.4122 16.2775 11.884C16.6939 11.3665 17.3308 10.941 18.2025 10.6141C20.6182 9.70826 21.8059 9.14269 22.2807 8.87507V7.14998H6.08074V7.22275V10.2142ZM17.8507 15.2228C17.4179 14.7752 17.0229 14.1213 17.0055 13.5287C16.9972 13.2484 17.0716 12.9708 17.2903 12.699C17.5176 12.4165 17.9307 12.1045 18.659 11.8314C21.4869 10.7709 22.7701 10.129 23.2 9.83556C23.4813 9.64351 23.5807 9.34202 23.5807 9.0985V6.79998C23.5807 6.2753 23.1554 5.84998 22.6307 5.84998H5.73074C5.28438 5.84998 4.90993 6.15781 4.80808 6.57275H0.695884C0.151987 6.57275 -0.316622 7.11431 -0.0935012 7.70385C0.393644 8.99102 1.89056 10.9646 4.78074 10.9199V10.9965C4.78074 11.4304 5.06628 11.7703 5.41632 11.9021C5.89027 12.0806 6.30904 12.4156 6.43924 12.864C6.55898 13.2765 6.50195 14.0283 5.50243 15.2228H4.43074C4.1546 15.2228 3.93074 15.4466 3.93074 15.7228V18.7228C3.93074 18.9989 4.1546 19.2228 4.43074 19.2228H18.4307C18.7069 19.2228 18.9307 18.9989 18.9307 18.7228V15.7228C18.9307 15.4466 18.7069 15.2228 18.4307 15.2228H17.8507ZM5.23074 17.9228V16.5228H17.6307V17.9228H5.23074Z" fill="currentColor"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_1957_6332">
                                <rect width="24" height="24" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                    <span>
                        Forge
                    </span>
                </router-link>
            </li>
            <li>
                <router-link to="/my-chests" :class="{ active_item: $route.path === '/my-chests' }">
                    <svg v-if="$route.path === '/my-chests'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M17.25 3.75H6.75C5.35807 3.75149 4.02358 4.30509 3.03933 5.28933C2.05509 6.27358 1.50149 7.60807 1.5 9V18C1.5 18.3978 1.65804 18.7794 1.93934 19.0607C2.22064 19.342 2.60218 19.5 3 19.5H21C21.3978 19.5 21.7794 19.342 22.0607 19.0607C22.342 18.7794 22.5 18.3978 22.5 18V9C22.4985 7.60807 21.9449 6.27358 20.9607 5.28933C19.9764 4.30509 18.6419 3.75149 17.25 3.75ZM21 9V9.75H18V5.325C18.8465 5.49904 19.607 5.95959 20.1535 6.62903C20.7 7.29846 20.9989 8.13584 21 9ZM12.75 12.75H11.25V9.75H12.75V12.75ZM10.5 14.25H13.5C13.6989 14.25 13.8897 14.171 14.0303 14.0303C14.171 13.8897 14.25 13.6989 14.25 13.5V11.25H16.5V18H7.5V11.25H9.75V13.5C9.75 13.6989 9.82902 13.8897 9.96967 14.0303C10.1103 14.171 10.3011 14.25 10.5 14.25ZM14.25 9.75V9C14.25 8.80109 14.171 8.61032 14.0303 8.46967C13.8897 8.32902 13.6989 8.25 13.5 8.25H10.5C10.3011 8.25 10.1103 8.32902 9.96967 8.46967C9.82902 8.61032 9.75 8.80109 9.75 9V9.75H7.5V5.25H16.5V9.75H14.25ZM6 5.325V9.75H3V9C3.00106 8.13584 3.30002 7.29846 3.8465 6.62903C4.39297 5.95959 5.15354 5.49904 6 5.325ZM3 11.25H6V18H3V11.25ZM21 18H18V11.25H21V18Z" fill="currentColor"/>
                    </svg>

                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M17.25 3.75H6.75C5.35807 3.75149 4.02358 4.30509 3.03933 5.28933C2.05509 6.27358 1.50149 7.60807 1.5 9V18C1.5 18.3978 1.65804 18.7794 1.93934 19.0607C2.22064 19.342 2.60218 19.5 3 19.5H21C21.3978 19.5 21.7794 19.342 22.0607 19.0607C22.342 18.7794 22.5 18.3978 22.5 18V9C22.4985 7.60807 21.9449 6.27358 20.9607 5.28933C19.9764 4.30509 18.6419 3.75149 17.25 3.75ZM21 9V9.75H18V5.325C18.8465 5.49904 19.607 5.95959 20.1535 6.62903C20.7 7.29846 20.9989 8.13584 21 9ZM12.75 12.75H11.25V9.75H12.75V12.75ZM10.5 14.25H13.5C13.6989 14.25 13.8897 14.171 14.0303 14.0303C14.171 13.8897 14.25 13.6989 14.25 13.5V11.25H16.5V18H7.5V11.25H9.75V13.5C9.75 13.6989 9.82902 13.8897 9.96967 14.0303C10.1103 14.171 10.3011 14.25 10.5 14.25ZM14.25 9.75V9C14.25 8.80109 14.171 8.61032 14.0303 8.46967C13.8897 8.32902 13.6989 8.25 13.5 8.25H10.5C10.3011 8.25 10.1103 8.32902 9.96967 8.46967C9.82902 8.61032 9.75 8.80109 9.75 9V9.75H7.5V5.25H16.5V9.75H14.25ZM6 5.325V9.75H3V9C3.00106 8.13584 3.30002 7.29846 3.8465 6.62903C4.39297 5.95959 5.15354 5.49904 6 5.325ZM3 11.25H6V18H3V11.25ZM21 18H18V11.25H21V18Z" fill="currentColor"/>
                    </svg>
                    <span>
                        Reward Chest
                    </span>
                </router-link>
            </li>

            <li>
                <router-link to="/feed" :class="{ active_item: $route.path === '/feed' }">
                    <svg v-if="$route.path === '/feed'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3.5625 8.0625H20.4375M17.625 12H6.375M6.375 21H17.625C19.489 21 21 19.489 21 17.625V6.375C21 4.51104 19.489 3 17.625 3H6.375C4.51104 3 3 4.51104 3 6.375V17.625C3 19.489 4.51104 21 6.375 21Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3.5625 8.0625H20.4375M17.625 12H6.375M6.375 21H17.625C19.489 21 21 19.489 21 17.625V6.375C21 4.51104 19.489 3 17.625 3H6.375C4.51104 3 3 4.51104 3 6.375V17.625C3 19.489 4.51104 21 6.375 21Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>
                        Feed
                    </span>
                </router-link>
            </li>
            <li>
                <router-link to="/exchange" :class="{ active_item: $route.path === '/exchange' }">
                    <svg v-if="$route.path === '/exchange'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M16.0253 20.5708L16.0256 17.3571C16.0258 15.5819 14.5867 14.1427 12.8115 14.1427H5.61432C3.83939 14.1427 2.40046 15.5815 2.40026 17.3564L2.3999 20.5708M21.5996 20.571L21.5999 17.3572C21.6001 15.5821 20.161 14.1429 18.3858 14.1429M15.4062 4.06061C16.1955 4.64624 16.7071 5.5851 16.7071 6.64343C16.7071 7.70176 16.1955 8.64061 15.4062 9.22625M12.4937 6.64325C12.4937 8.41833 11.0547 9.85731 9.27964 9.85731C7.50457 9.85731 6.06559 8.41833 6.06559 6.64325C6.06559 4.86818 7.50457 3.4292 9.27964 3.4292C11.0547 3.4292 12.4937 4.86818 12.4937 6.64325Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M16.0254 20.5708L16.0257 17.3571C16.0259 15.5819 14.5869 14.1427 12.8117 14.1427H5.61444C3.83951 14.1427 2.40059 15.5815 2.40039 17.3564L2.40002 20.5708M21.5997 20.571L21.6 17.3572C21.6002 15.5821 20.1612 14.1429 18.386 14.1429M15.4064 4.06061C16.1957 4.64624 16.7072 5.5851 16.7072 6.64343C16.7072 7.70176 16.1957 8.64061 15.4064 9.22625M12.4938 6.64325C12.4938 8.41833 11.0548 9.85731 9.27977 9.85731C7.50469 9.85731 6.06571 8.41833 6.06571 6.64325C6.06571 4.86818 7.50469 3.4292 9.27977 3.4292C11.0548 3.4292 12.4938 4.86818 12.4938 6.64325Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>
                        Exchange
                    </span>
                </router-link>
            </li>
            <li>
                <router-link to="/network" :class="{ active_item: $route.path === '/network' }">
                    <svg v-if="$route.path === '/network'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M15.5709 1.57129L21.2852 10.7141L15.5709 19.857L6.42801 19.857L0.713729 10.7141L6.42801 1.57129L15.5709 1.57129Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="10.9997" cy="10.7141" r="6.20714" stroke="black" stroke-width="1.3"/>
                        <circle cx="10.9997" cy="10.7141" r="6.20714" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M8.42773 16.5713L6.71345 19.5713" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.2852 1.57129L13.5709 4.57129" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 10.7144L17.5714 10.7144" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.71345 1.57129L8.42773 4.99986" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.28432 10.7144L4.71289 10.7144" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5709 16.4282L15.2852 19.8568" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M15.5718 1.57129L21.2861 10.7141L15.5718 19.857L6.42899 19.857L0.714705 10.7141L6.42899 1.57129L15.5718 1.57129Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="11.0007" cy="10.7141" r="6.20714" stroke="black" stroke-width="1.3"/>
                        <circle cx="11.0007" cy="10.7141" r="6.20714" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M8.42871 16.5713L6.71442 19.5713" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.2861 1.57129L13.5718 4.57129" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 10.7144L17.5714 10.7144" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.71443 1.57129L8.42871 4.99986" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1.28627 10.7144L4.71484 10.7144" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.5718 16.4282L15.2861 19.8568" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>
                        Network
                    </span>
              </router-link>
            </li>
            <li v-if="isAdmin">
                <router-link to="/brand-dashboard" :class="{ active_item: $route.path === '/brand-dashboard' }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.3"/>
                        <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.3"/>
                        <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.3"/>
                        <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.3"/>
                    </svg>
                    <span>Brand Dashboard</span>
                </router-link>
            </li>
        </ul>
        <ul class="sidebar_footer_links">
            <li @click="redirectToTwitter" class="cursor-pointer">
                    <img src="../../../web/images/web/img/social_icons/twitter.svg" alt="twitter" title="twitter">
            </li>
            <li @click="redirectToDiscord" class="cursor-pointer">
                    <img src="../../../web/images/web/img/social_icons/discord.svg" alt="discord" title="discord">
            </li>
            <li @click="redirectToSupport" class="cursor-pointer">
                    <img style="max-width: 24px; max-height: 24px" src="../../../web/images/web/img/social_icons/support.svg" alt="twitch" title="support">
            </li>
        </ul>
    </div>
</template>

<script>
import store from "../store/store.js";
export default {
    data() {
        return {
            windowWidth: window.innerWidth,
        }
    },
    computed: {
        sideBarStatus() {
            return store.state.activeSideBar;
        },
        isMobile() {
            return this.windowWidth <= 968;
        },
        isAdmin() {
            const roles = store.state.user?.roles;
            if (!roles) return false;
            return Array.isArray(roles)
                ? roles.some(r => r === 'admin' || r?.name === 'admin')
                : false;
        },
    },
    methods: {
        closeSideBar() {
            store.state.activeSideBar = false;
        },
        handleResize() {
            this.windowWidth = window.innerWidth;
            // console.log(this.windowWidth)
        },
      redirectToTwitter(){
        window.open('https://twitter.com/TrophyRoomGG', '_blank');
      },
      redirectToDiscord(){
        window.open('https://discord.gg/3sGk8uGQBT', '_blank');
      },
      redirectToSupport(){
        window.open('https://trophyroom.gg/support', '_blank');
      },
    },
    created() {
        window.addEventListener('resize', this.handleResize);
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize);
    },
}
</script>

<style>
.cursor-pointer{
  cursor: pointer;
}
</style>
