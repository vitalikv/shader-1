<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Webgl-editor</title>
    
  
</head>

<body>


<script src="js/three.min.js"></script>
<script src="js/jquery.js"></script>

<script src="js/OrbitControls.js"></script>



<div class='canvas-container'></div>

<script id='sphere-vertex-shader' type='x-shader/x-vertex'>
    varying vec2 vUv;

    void main() {
        vUv = uv;

        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
    }
</script>

<script id='sphere-fragment-shader' type='x-shader/x-fragment'>
    varying vec2 vUv;

    void main() 
	{
        if ((vUv.x < -14.0) || (vUv.x > 14.0) || (vUv.y < -31.0) || (vUv.y > 31.0)) 
		{
            gl_FragColor = vec4(vec3(0.0), 1.0);
        } 
		else 
		{
            gl_FragColor = vec4(1.0);
        }
    }
</script>


<script>

let SCENE;
let CAMERA;
let RENDERER;
let CONTROLS;


main();


function main() {
    init();
    animate();
}


function init() {
    initScene();
    initCamera();
    initRenderer();
    initControls();
    initEventListeners();

    createObjects();
	createGrid();

    document.querySelector('.canvas-container').appendChild(RENDERER.domElement);
}


function initScene() {
    SCENE = new THREE.Scene();

    initLights();
}


function initLights() {
    const point = new THREE.PointLight(0xffffff, 1, 0);
    point.position.set(0, 100, 50);
    SCENE.add(point);
}


function initCamera() {
    CAMERA = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 2000);
    CAMERA.position.y = 100;
    CAMERA.position.z = 100;
}


function initRenderer() {
    RENDERER = new THREE.WebGLRenderer({ alpha: true });
    RENDERER.setPixelRatio(window.devicePixelRatio);
    RENDERER.setSize(window.innerWidth, window.innerHeight);
    RENDERER.shadowMap.enabled = true;
    RENDERER.shadowMapSort = true;
    RENDERER.setClearColor(0xffffff, 1);
}


function initControls() {
    CONTROLS = new THREE.OrbitControls(CAMERA);
    //CONTROLS.enableZoom = false;
    //CONTROLS.minPolarAngle = Math.PI * 1 / 4;
    //CONTROLS.maxPolarAngle = Math.PI * 3 / 4;
    CONTROLS.update();
}


function initEventListeners() {
    window.addEventListener('resize', onWindowResize);

    onWindowResize();
}


function onWindowResize() {
    CAMERA.aspect = window.innerWidth / window.innerHeight;
    CAMERA.updateProjectionMatrix();

    RENDERER.setSize(window.innerWidth, window.innerHeight);
}


function animate() {
    requestAnimationFrame(animate);
    CONTROLS.update();
    render();
}

function render() {
    CAMERA.lookAt(SCENE.position);
    RENDERER.render(SCENE, CAMERA);
}


function createObjects() {
    const geometry = new THREE.BoxGeometry(30, 64, 64);
    const shaderMaterial = new THREE.ShaderMaterial({
        uniforms: {
            //...
        },
        vertexShader:   document.getElementById('sphere-vertex-shader').textContent,
        fragmentShader: document.getElementById('sphere-fragment-shader').textContent
    });
    const sphere = new THREE.Mesh(geometry, shaderMaterial);
	
	upUvs_4( sphere );

    SCENE.add(sphere);
}


function createGrid() 
{	
	var geometry = new THREE.PlaneGeometry( 100, 100 );
	var material = new THREE.MeshLambertMaterial( {color: 0x00ff00, side: THREE.DoubleSide } );
	
	var vertexShader = `
		varying vec2 vUv;
		void main() 
		{
			vUv = uv;
			gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
		}
	`;
  
	var fragmentShader = `	
	#ifdef GL_ES
	precision mediump float;
	#endif
	varying vec2 vUv;
	
	uniform vec2 u_resolution;

	float random(in float x){ return fract(sin(x)*43758.5453); }
	float random(in vec2 st){ return fract(sin(dot(st.xy ,vec2(12.9898,78.233))) * 43758.5453); }

	float grid(vec2 st, float res){
		vec2 grid = fract(st*res);
		return 1.-(step(res,grid.x) * step(res,grid.y));
	}


	void main(){
		vec2 st = gl_FragCoord.st/u_resolution.xy;
		st.x *= u_resolution.x/u_resolution.y;

		vec3 color = vec3(0.0);

		// Grid
		vec2 grid_st = st*100.;
		color += vec3(0.5,0.,0.)*grid(grid_st,0.01);
		color += vec3(0.2,0.,0.)*grid(grid_st,0.02);
		color += vec3(0.2)*grid(grid_st,0.1);



		gl_FragColor = vec4( color , 1.0);
	}`;
	
    var uniforms = {
      u_resolution:  { value: new THREE.Vector2(window.innerWidth, window.innerHeight) },
    };
	
    var material = new THREE.ShaderMaterial({ vertexShader, fragmentShader, uniforms });
  
	
	var grid = new THREE.Mesh( geometry, material );		

    SCENE.add(grid);
}




//------------------

function upUvs_4( obj )
{
	obj.updateMatrixWorld();
	var geometry = obj.geometry;
	
    geometry.faceVertexUvs[0] = [];
	var faces = geometry.faces;
	
    for (var i = 0; i < faces.length; i++) 
	{		
		var components = ['x', 'y', 'z'].sort(function(a, b) {			
			return Math.abs(faces[i].normal[a]) - Math.abs(faces[i].normal[b]);
		});	


        var v1 = geometry.vertices[faces[i].a];
        var v2 = geometry.vertices[faces[i].b];
        var v3 = geometry.vertices[faces[i].c];				

        geometry.faceVertexUvs[0].push([
            new THREE.Vector2(v1[components[0]], v1[components[1]]),
            new THREE.Vector2(v2[components[0]], v2[components[1]]),
            new THREE.Vector2(v3[components[0]], v3[components[1]])
        ]);
    }

    geometry.uvsNeedUpdate = true;
	geometry.elementsNeedUpdate = true; 
}

</script>

 

</body>

</html>