<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF" crossorigin="anonymous"></script>
    <style>
        .img-preview-new {
            max-width: 100%;
            height: 100px;
        }
    </style>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary" onclick="add()">
                Tambah Data
            </button>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-md-12">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <td>No</td>
                        <td>Nama Project</td>
                        <td>Action</td>
                    </tr>
                </thead>
                <tbody>
                @foreach($projects as $key => $project)
                    @php
                    $editAction = route('edit.project', ['project' => $project->id]);
                    $deleteAction = route('delete.project', ['project' => $project->id])
                    @endphp
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ $project->name }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="edit('{{ $editAction }}')">
                                Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteProject('{{ $deleteAction }}')">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('store.project') }}" id="form" method="POST" enctype="multipart/form-data">
                @csrf()
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Nama Project</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="input nama project">
                            <div id="name-feedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="image">Gambar Project</label>
                            <input type="file" multiple id="images" name="images[]" class="form-control">
                            <div id="images-feedback" class="invalid-feedback"></div>
                            <div class="new-image row"></div>
                            <div class="exists-image row"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    let previewNewImage = function (input, place) {
        if (input.files) {
            let filesAmount = input.files.length;

            for (let i = 0; i < filesAmount; i++) {
                let reader = new FileReader();

                reader.onload = function(event) {
                    let imageContainer = `<div class="col-3">`+
                        `<img class="img-preview-new" src="${event.target.result}" alt="">`+
                        `<button type="button" onclick="deleteFile(${i})" class="btn btn-danger btn-sm w-100 mb-0">Hapus</button>`+
                    `</div>`;
                    $(place).append(imageContainer);
                }

                reader.readAsDataURL(input.files[i]);
            }
        }
    }

    let previewExistsImage = function (data, place) {
        for (let i = 0; i < data.length; i++) {
            let imageContainer = `<div class="col-3">`+
                `<img class="img-preview-new" src="${data[i]['image_path']}" alt="">`+
                `<button type="button" onclick="deleteExistsFile(${data[i]['id']}, ${data[i]['project_id']})" class="btn btn-danger btn-sm w-100 mb-0">Hapus</button>`+
                `</div>`;
            $(place).append(imageContainer);
        }
    }

    // handle remove image (before uploaded file)
    $('#images').on('change', function () {
        $('.new-image').html('');
        previewNewImage(this, '.new-image')
    })

    function deleteFile(index) {
        let dt = new DataTransfer()
        let input = document.getElementById('images');
        let { files } = input

        for (let i = 0; i < files.length; i++) {
            let file = files[i]
            if (index !== i) dt.items.add(file)
            input.files = dt.files
        }

        $('#images').trigger('change');
    }

    function deleteExistsFile(id, project_id) {
        let url = '{{ route('delete.image', ':id') }}';
        url = url.replace(':id', id);

        $.ajax({
            type: 'DELETE',
            url: url,
            success: function (data) {
                getImageByProject(project_id);
            }
        })
    }

    function getImageByProject(project_id) {
        $('.exists-image').html('');
        let url = '{{ route('get.image.project', ':id') }}';
        url = url.replace(':id', project_id);

        $.ajax({
            type: 'GET',
            url: url,
            success: function (data) {
                previewExistsImage(data, '.exists-image');
            }
        })
    }

    $('#form').submit(function (e) {
        e.preventDefault();
        let formData = new FormData($(this)[0]);
        let form = $(this);

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            enctype: 'multipart/form-data',
            data: formData,
            processData: false,
            contentType: false,
            success: function (data, textStatus, xhr) {
                alert('Simpan data berhasil');
                window.location.reload();
            },
            error: function (data) {
                let errors = data['responseJSON']['errors'];
                if (data.status === 422) {
                    for (let error in errors) {
                        let field = error;
                        if (field.includes('images')){
                            $('#images').addClass('is-invalid');
                            console.log(errors[error][0]);
                            $('#images-feedback').text(errors[error][0]);
                        } else {
                            $(`#${field}`).addClass('is-invalid');
                            $(`#${field}-feedback`).text(errors[error][0]);
                        }
                    }
                }
            }
        });
    });

    function add() {
        $('.exists-image').html('');
        $('.new-image').html('');
        $('.invalid-feedback').html('');
        $('.form-control').removeClass('is-invalid');
        $('#exampleModal').modal('show')
    }

    function edit(url) {
        $('#form').attr('action', url);
        $('.exists-image').html('');
        $('.new-image').html('');
        $('.invalid-feedback').html('');
        $('.form-control').removeClass('is-invalid');
        $.ajax({
            type: 'GET',
            url: url,
            success: function (data) {
                $('#name').val(data.name)
                previewExistsImage(data.images, '.exists-image')
                $('#exampleModal').modal('show')
            }
        })
    }

    function deleteProject(url) {
        $.ajax({
            type: 'DELETE',
            url: url,
            success: function (data) {
                window.location.reload();
            }
        })
    }
</script>
</body>
</html>
