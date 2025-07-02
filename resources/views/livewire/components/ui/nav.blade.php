 <?php
 
 use App\Livewire\Actions\Logout;
 use Livewire\Volt\Component;
 
 new class extends Component {
     /**
      * Log the current user out of the application.
      */
     public function logout(Logout $logout): void
     {
         $logout();
 
         $this->redirect(route('login'), navigate: true);
     }
 }; ?>
 <nav class="fixed z-30 w-full border-b bg-surface border-border dark:bg-surface-dark dark:border-border-dark">
     <div class="px-3 py-3 lg:px-5 lg:pl-3">
         <div class="flex items-center justify-between">
             <div class="flex items-center justify-start">
                 <button id="toggleSidebar" aria-expanded="true" aria-controls="sidebar"
                     class="hidden p-2 mr-3 text-text rounded cursor-pointer lg:inline hover:text-text hover:bg-surface focus:ring-2 focus:ring-border dark:text-text-dark dark:hover:text-text-dark dark:hover:bg-surface-dark dark:focus:ring-border-dark">
                     <x-icon name="fas-bars-staggered" class="w-6 h-6" />
                 </button>

                 <button id="toggleSidebarMobile" aria-expanded="true" aria-controls="sidebar"
                     class="p-2 mr-2 text-text rounded cursor-pointer lg:hidden hover:text-text hover:bg-surface focus:bg-surface focus:ring-2 focus:ring-border dark:text-text-dark dark:hover:text-text-dark dark:hover:bg-surface-dark dark:focus:bg-surface-dark dark:focus:ring-border-dark">

                     <x-icon name="fas-bars-staggered" id="toggleSidebarMobileHamburger" class="w-6 h-6" />
                     <x-icon name="fas-xmark" id="toggleSidebarMobileClose" class="hidden w-6 h-6" />
                 </button>

                 <a href="./" class="flex mr-14">
                     <img src="../images/logo.svg" class="h-8 mr-3" alt="Logo" />
                     <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"
                         x-data="{ siteName: '{{ setting('site_name') }}' }" x-init="window.addEventListener('site-name-updated', e => {
                             siteName = e.detail.site_name;
                         })"
                         x-text="siteName"></span>
                 </a>

             </div>
             <div class="flex items-center">
                 <x-ui.theme-toggle class="ml-3" />

                 <div class="ml-3">
                     <div>
                         @php
                             $colors = [
                                 'bg-red-500',
                                 'bg-green-500',
                                 'bg-blue-500',
                                 'bg-yellow-500',
                                 'bg-indigo-500',
                                 'bg-purple-500',
                                 'bg-pink-500',
                                 'bg-teal-500',
                                 'bg-amber-500',
                                 'bg-lime-500',
                                 'bg-cyan-500',
                             ];
                         @endphp

                         <div x-data="avatarComponent(@js(auth()->user()->name), @js($colors))" x-on:profile-updated.window="update($event.detail.name)"
                             class="flex items-center">
                             <button type="button"
                                 class="flex items-center justify-center w-8 h-8 text-sm text-white rounded-full focus:ring-4 focus:ring-primary/30 dark:focus:ring-primary/50"
                                 :class="bgColor" id="user-menu-button-2" aria-expanded="false"
                                 data-dropdown-toggle="dropdown-2">
                                 <span class="sr-only">Open user menu</span>
                                 <span x-text="initials"></span>
                             </button>
                         </div>




                     </div>

                     <div class="z-50 hidden my-4 text-base list-none bg-surface divide-y divide-border rounded shadow dark:bg-surface-dark dark:divide-border-dark"
                         id="dropdown-2">
                         <div class="px-4 py-3" role="none">
                             <p class="text-sm text-text dark:text-text-dark" role="none">
                                 <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                                     x-on:profile-updated.window="name = $event.detail.name"></span>
                             </p>
                             <p class="text-sm font-medium text-text/80 truncate dark:text-text-dark/80" role="none">
                                 <span x-data="{{ json_encode(['email' => auth()->user()->email]) }}" x-text="email"
                                     x-on:profile-updated.window="email = $event.detail.email"></span>
                             </p>
                             @foreach (auth()->user()->getRoleNames() as $role)
                                 <span
                                     class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary dark:bg-primary-dark/10 dark:text-primary-dark mr-1">
                                     {{ $role }}
                                 </span>
                             @endforeach


                         </div>
                         <ul class="py-1" role="none">
                             <li>
                                 <a href="#"
                                     class="block px-4 py-2 text-sm text-text/80 hover:bg-surface-dark/10 dark:text-text-dark/80 dark:hover:bg-surface/10 dark:hover:text-text-dark"
                                     role="menuitem">Dashboard</a>
                             </li>
                             <li>
                                 <a href="{{ route('profile') }}" wire:navigate
                                     class="block px-4 py-2 text-sm text-text/80 hover:bg-surface-dark/10 dark:text-text-dark/80 dark:hover:bg-surface/10 dark:hover:text-text-dark"
                                     role="menuitem">Profile</a>
                             </li>
                             <li>
                                 <a href="#"
                                     class="block px-4 py-2 text-sm text-text/80 hover:bg-surface-dark/10 dark:text-text-dark/80 dark:hover:bg-surface/10 dark:hover:text-text-dark"
                                     role="menuitem">Earnings</a>
                             </li>
                             <li>
                                 <a wire:click="logout"
                                     class="block cursor-pointer px-4 py-2 text-sm text-text/80 hover:bg-surface-dark/10 dark:text-text-dark/80 dark:hover:bg-surface/10 dark:hover:text-text-dark"
                                     role="menuitem">Sign out</a>
                             </li>
                         </ul>
                     </div>

                 </div>
             </div>
         </div>
     </div>
 </nav>
