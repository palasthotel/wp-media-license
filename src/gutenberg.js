import ListOfLicenses from "./components/ListOfLicenses";

window.BlockXComponents = {
    ...(window.BlockXComponents || {}),
    ["media-license/list-of-licenses"]: (props)=> <ListOfLicenses
        {...props}
    />,
};